<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PostcardExport;
// use App\Exports\PostcardPrintExport; // not used; CSV/XLSX builders inline
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class PaymentController extends Controller
{
    /**
     * Calculate totals for a set of items.
     * Returns [subtotal, tax_total, gross_total].
     */
    private function computeTotalsForItems($items): array
    {
        if ($items instanceof Collection) {
            $collection = $items;
        } else {
            $collection = collect($items ?? []);
        }
        $subtotal = (float) $collection->sum(function ($it) {
            return (float) ($it->amount ?? (($it->unit_price ?? 0) * ($it->quantity ?? 0)));
        });
        $taxTotal = (float) $collection->sum(function ($it) {
            return (float) ($it->tax_amount ?? 0);
        });
        return [
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'gross_total' => $subtotal + $taxTotal,
        ];
    }

    /**
     * Consolidate duplicate display items by normalized product name, summing amounts.
     */
    private function consolidateDisplayItems(array $items): array
    {
        $grouped = [];
        foreach ($items as $it) {
            $name = (string)($it['name'] ?? '');
            $key = mb_strtolower(preg_replace('/\s+/u', '', $name));
            if ($key === '') {
                continue;
            }
            $amount = (float)($it['amount'] ?? 0);
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'date' => $it['date'] ?? '',
                    'name' => $name,
                    'amount' => $amount,
                ];
            } else {
                $grouped[$key]['amount'] += $amount;
            }
        }
        return array_values($grouped);
    }

    /**
     * Normalize postal code (e.g., 1234567 -> 123-4567).
     */
    private function formatPostalCode(?string $code): string
    {
        $raw = preg_replace('/[^0-9]/', '', (string)($code ?? ''));
        if ($raw === '') {
            return '';
        }
        if (strlen($raw) === 7) {
            return substr($raw, 0, 3) . '-' . substr($raw, 3);
        }
        return $code ?? '';
    }

    /**
     * Build best-effort address and postal code from customer record.
     */
    private function buildCustomerAddress(Customer $customer): array
    {
        $postal = $customer->postal_code ?? $customer->billing_postal_code ?? '';
        $postal = $this->formatPostalCode($postal);

        $address = trim((string)($customer->address ?? ''));
        if ($address === '') {
            $parts = [
                $customer->billing_prefecture ?? '',
                $customer->billing_city ?? '',
                $customer->billing_street ?? '',
                $customer->billing_building ?? '',
            ];
            $address = trim(implode(' ', array_values(array_filter($parts, function ($v) {
                return (string)trim((string)$v) !== '';
            }))));
        }

        return [$postal, $address];
    }
    private function loadJapaneseFontBase64(): ?string
    {
        $candidateFontPaths = [
            resource_path('fonts/NotoSansJP.ttf'),
            resource_path('fonts/NotoSansJP-Regular.ttf'),
            resource_path('fonts/ipag.ttf'),
            resource_path('fonts/ipaexg.ttf'),
            resource_path('fonts/ipaexm.ttf'),
        ];

        foreach ($candidateFontPaths as $fontPath) {
            if (file_exists($fontPath)) {
                $fontBinary = @file_get_contents($fontPath);
                if ($fontBinary !== false) {
                    return base64_encode($fontBinary);
                }
            }
        }
        return null;
    }

    /**
     * Determine whether the header row corresponds to the detailed deposit XLSX format.
     */
    private function looksLikeDetailedDepositHeader($header): bool
    {
        if (!is_array($header)) {
            return false;
        }
        $joined = implode('|', array_map(function ($h) {
            return trim((string)$h);
        }, $header));
        $requiredKeywords = ['対象年月', '顧客', '商品名'];
        foreach ($requiredKeywords as $kw) {
            if (mb_strpos($joined, $kw) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Import the detailed deposit XLSX format shown in the screenshot.
     */
    private function importDetailedDepositXlsx(array $rows, array $header, array $options = []): array
    {
        $errors = [];
        $groups = [];

        // Map header titles to indices
        $index = [];
        foreach ($header as $i => $h) {
            $key = trim((string)$h);
            $index[$key] = $i;
        }

        $ymCol = $this->findHeaderIndex($index, ['対象年月', '年月', '対象月']);
        $customerCodeCol = $this->findHeaderIndex($index, ['顧客CD', '顧客番号', '顧客No']);
        $customerNameCol = $this->findHeaderIndex($index, ['氏名', '利用者氏名']);
        $productNameCol = $this->findHeaderIndex($index, ['商品名']);
        $quantityCol = $this->findHeaderIndex($index, ['数量']);
        $unitPriceCol = $this->findHeaderIndex($index, ['単価']);
        $amountCol = $this->findHeaderIndex($index, ['金額']);
        $taxCol = $this->findHeaderIndex($index, ['消費税', '税']);
        $payMethodCol = $this->findHeaderIndex($index, ['支払方法']);

        if ($ymCol === null || ($customerNameCol === null && $customerCodeCol === null) || $productNameCol === null || $amountCol === null) {
            $errors[] = 'ヘッダーの必須列が見つかりません (対象年月, 氏名/顧客CD, 商品名, 金額)。';
            return [0, $errors];
        }

        foreach ($rows as $rowNo => $row) {
            if (empty(array_filter($row, function ($v) {
                return (string)trim((string)$v) !== '';
            }))) {
                continue;
            }
            $customerCode = trim((string)($row[$customerCodeCol] ?? ''));
            $customerName = trim((string)($row[$customerNameCol] ?? ''));
            $targetYm = trim((string)($row[$ymCol] ?? ''));
            if (($customerName === '' && $customerCode === '') || $targetYm === '') {
                $errors[] = ($rowNo + 2) . '行目: 氏名/顧客CDまたは対象年月が空です。';
                continue;
            }
            $groupKey = ($customerName !== '' ? $customerName : $customerCode) . '|' . $targetYm;
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'target_ym' => $targetYm,
                    // Use map to allow overwriting same-name items within one import
                    'items_by_key' => [],
                ];
            }

            $quantity = $this->parseDecimal($row[$quantityCol] ?? null);
            $unitPrice = $this->parseDecimal($row[$unitPriceCol] ?? null);
            $amount = $this->parseDecimal($row[$amountCol] ?? null);
            $tax = $this->parseDecimal($row[$taxCol] ?? null);
            $name = trim((string)($row[$productNameCol] ?? ''));
            $norm = mb_strtolower(preg_replace('/\s+/u', '', $name));
            $groups[$groupKey]['items_by_key'][$norm] = [
                'row_no' => $rowNo + 2,
                'product_name' => $name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'amount' => $amount,
                'tax_amount' => $tax,
                'pay_method' => ($payMethodCol !== null) ? trim((string)($row[$payMethodCol] ?? '')) : null,
            ];
        }

        $imported = 0;

        DB::beginTransaction();
        try {
            foreach ($groups as $group) {
                $customer = null;
                if (!empty($group['customer_name'])) {
                    $customer = $this->findCustomerByName($group['customer_name']);
                }
                if (!$customer && !empty($group['customer_code'])) {
                    $customer = Customer::where('customer_number', $group['customer_code'])
                        ->orWhere('customer_code', $group['customer_code'])
                        ->first();
                }
                if (!$customer) {
                    $label = !empty($group['customer_name']) ? $group['customer_name'] : $group['customer_code'];
                    $errors[] = "顧客 '{$label}' が見つかりません。";
                    continue;
                }

                [$year, $month] = $this->parseYearMonth($group['target_ym']);
                if (!$year || !$month) {
                    $errors[] = "顧客 '{$customer->name}' の対象年月 '{$group['target_ym']}' を解析できません。";
                    continue;
                }

                $items = array_values($group['items_by_key']);
                $subtotal = 0.0;
                $taxTotal = 0.0;
                foreach ($items as $r) {
                    $subtotal += (float)($r['amount'] ?? 0);
                    $taxTotal += (float)($r['tax_amount'] ?? 0);
                }
                $grandTotal = $subtotal + $taxTotal;

                // Do NOT set payment_date at capture time; keep it null until results import
                $paymentDate = null;

                // Overwrite existing payment for this customer/month
                $payment = Payment::where('customer_id', $customer->id)
                    ->where('payment_month', $month)
                    ->where('payment_year', $year)
                    ->first();

                if ($payment) {
                    $payment->update([
                        'amount' => $grandTotal,
                        'subtotal_amount' => $subtotal ?: null,
                        'tax_total' => $taxTotal ?: null,
                        'grand_total' => $grandTotal ?: null,
                        'payment_date' => $paymentDate,
                        'status' => 'pending',
                        'notes' => '詳細XLSXから取込(上書き)',
                    ]);
                    $payment->items()->delete();
                } else {
                    $payment = Payment::create([
                        'customer_id' => $customer->id,
                        'payment_month' => $month,
                        'payment_year' => $year,
                        'amount' => $grandTotal,
                        'subtotal_amount' => $subtotal ?: null,
                        'tax_total' => $taxTotal ?: null,
                        'grand_total' => $grandTotal ?: null,
                        'payment_date' => $paymentDate,
                        'status' => 'pending',
                        'notes' => '詳細XLSXから取込',
                    ]);
                }

                foreach ($items as $idx => $r) {
                    PaymentItem::create([
                        'payment_id' => $payment->id,
                        'row_no' => $r['row_no'] ?? ($idx + 1),
                        'item_date' => null,
                        'product_code' => null,
                        'product_name' => $r['product_name'] ?? '',
                        'unit_price' => $r['unit_price'] ?? null,
                        'quantity' => $r['quantity'] ?? 1,
                        'amount' => $r['amount'] ?? 0,
                        'tax_rate' => null,
                        'tax_amount' => $r['tax_amount'] ?? 0,
                        'category' => null,
                        'notes' => $r['pay_method'] ? ('支払方法: ' . $r['pay_method']) : null,
                    ]);
                }

                $imported++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $errors[] = '詳細XLSX取込でエラー: ' . $e->getMessage();
        }

        return [$imported, $errors];
    }

    private function findHeaderIndex(array $headerIndex, array $candidates): ?int
    {
        foreach ($candidates as $title) {
            if (array_key_exists($title, $headerIndex)) {
                return $headerIndex[$title];
            }
        }
        // fallback: loose contains match
        foreach ($headerIndex as $label => $idx) {
            foreach ($candidates as $title) {
                if ($label !== '' && mb_strpos($label, $title) !== false) {
                    return $idx;
                }
            }
        }
        return null;
    }

    private function findCustomerByName(string $name)
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            return null;
        }
        // Try exact match on user_name
        $customer = Customer::where('user_name', $trimmed)->first();
        if ($customer) {
            return $customer;
        }
        // Try contains match as fallback
        return Customer::where('user_name', 'like', '%' . $trimmed . '%')->first();
    }

    private function parseDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $str = (string)$value;
        // Normalize Excel-style numbers: remove yen marks, commas, full-width digits, stray backslashes
        $str = str_replace(['\\', '¥', '￥', ',', '，', ' '], '', $str);
        $str = preg_replace('/\s+/u', '', $str);
        // Convert full-width digits to half-width
        $str = mb_convert_kana($str, 'n');
        // Keep only digits, dot, minus
        $clean = preg_replace('/[^0-9.\-]/u', '', $str);
        if ($clean === '' || !is_numeric($clean)) {
            return null;
        }
        return (float)$clean;
    }

    private function formatCurrency(float $value): string
    {
        return number_format($value, (fmod($value, 1.0) === 0.0 ? 0 : 2));
    }

    private function parseYearMonth(string $text): array
    {
        $t = trim((string)$text);
        if ($t === '') {
            return [null, null];
        }

        // Normalize: convert full-width to half-width, strip spaces
        $norm = mb_convert_kana($t, 'nask');
        $norm = preg_replace('/\s+/u', '', $norm);

        // Exact formats
        if (preg_match('/^(\d{4})[\/-](\d{1,2})$/', $norm, $m)) {
            return [(int)$m[1], (int)$m[2]];
        }
        if (preg_match('/^(\d{4})年\s*(\d{1,2})月/u', $norm, $m)) {
            return [(int)$m[1], (int)$m[2]];
        }

        // Find the last plausible YYYY/MM inside noisy text
        if (preg_match_all('/(\d{4})[\/-](\d{1,2})/', $norm, $matches, PREG_SET_ORDER)) {
            $m = end($matches);
            $mm = (int)$m[2];
            if ($mm >= 1 && $mm <= 12) {
                return [(int)$m[1], $mm];
            }
        }
        // Compact yyyymm appearing inside text
        if (preg_match_all('/(\d{4})(\d{2})/', $norm, $matches, PREG_SET_ORDER)) {
            foreach (array_reverse($matches) as $m) {
                $yy = (int)$m[1];
                $mm = (int)$m[2];
                if ($mm >= 1 && $mm <= 12) {
                    return [$yy, $mm];
                }
            }
        }
        // Excel serial date
        if (preg_match('/^\d+$/', $norm)) {
            $serial = (int)$norm;
            if ($serial >= 59 && $serial <= 60000) {
                try {
                    $d = \Carbon\Carbon::create(1899, 12, 30)->addDays($serial);
                    return [$d->year, $d->month];
                } catch (\Exception $e) {
                }
            }
        }

        // Fallback to Carbon parse
        try {
            $dt = \Carbon\Carbon::parse($norm);
            return [$dt->year, $dt->month];
        } catch (\Exception $e) {
            return [null, null];
        }
    }
    private function buildPostcardData(int $currentMonth, int $currentYear): array
    {
        $previousMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
        $previousYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;
        $customers = Customer::with([
            'payments' => function ($query) use ($currentMonth, $currentYear, $previousMonth, $previousYear) {
                $query->where(function ($q) use ($currentMonth, $currentYear) {
                    $q->where('payment_month', $currentMonth)
                        ->where('payment_year', $currentYear);
                })->orWhere(function ($q) use ($previousMonth, $previousYear) {
                    $q->where('payment_month', $previousMonth)
                        ->where('payment_year', $previousYear);
                });
            }
        ])->whereHas('payments', function ($query) use ($currentMonth, $currentYear, $previousMonth, $previousYear) {
            $query->where(function ($q) use ($currentMonth, $currentYear) {
                $q->where('payment_month', $currentMonth)
                    ->where('payment_year', $currentYear);
            })->orWhere(function ($q) use ($previousMonth, $previousYear) {
                $q->where('payment_month', $previousMonth)
                    ->where('payment_year', $previousYear);
            });
        })->get();

        $postcardData = [];
        foreach ($customers as $customer) {
            $currentPayment = $customer->payments->where('payment_month', $currentMonth)
                ->where('payment_year', $currentYear)
                ->first();

            $previousPayment = $customer->payments->where('payment_month', $previousMonth)
                ->where('payment_year', $previousYear)
                ->first();

            $postcardData[] = [
                'customer' => $customer,
                'current_month_name' => date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)),
                'current_payment' => $currentPayment,
                'previous_month_name' => date('F Y', mktime(0, 0, 0, $previousMonth, 1, $previousYear)),
                'previous_payment' => $previousPayment,
            ];
        }
        return $postcardData;
    }

    private function generatePostcardPrintData(int $month, int $year, int $offset = 0, int $limit = 200): array
    {
        $previousMonth = $month === 1 ? 12 : $month - 1;
        $previousYear = $month === 1 ? $year - 1 : $year;

        // Collect customers who have either current month invoice or previous month receipt
        $customers = Customer::with([
            'payments' => function ($query) use ($month, $year, $previousMonth, $previousYear) {
                $query->where(function ($q) use ($month, $year) {
                    $q->where('payment_month', $month)
                        ->where('payment_year', $year);
                })->orWhere(function ($q) use ($previousMonth, $previousYear) {
                    $q->where('payment_month', $previousMonth)
                        ->where('payment_year', $previousYear);
                });
            },
            'payments.items'
        ])->whereHas('payments', function ($query) use ($month, $year, $previousMonth, $previousYear) {
            $query->where(function ($q) use ($month, $year) {
                $q->where('payment_month', $month)
                    ->where('payment_year', $year);
            })->orWhere(function ($q) use ($previousMonth, $previousYear) {
                $q->where('payment_month', $previousMonth)
                    ->where('payment_year', $previousYear);
            });
        })->orderBy('id')->skip(max(0, $offset))->take(max(1, $limit))->get();

        $rows = [];
        foreach ($customers as $customer) {
            $currentPayment = $customer->payments->where('payment_month', $month)
                ->where('payment_year', $year)
                ->first();
            $previousPayment = $customer->payments->where('payment_month', $previousMonth)
                ->where('payment_year', $previousYear)
                ->first();

            // Resolve postal code and address reliably
            [$pc, $addr] = $this->buildCustomerAddress($customer);

            // Build current items
            $currentItems = [];
            $transferFee = 0.0;
            if ($currentPayment) {
                foreach ($currentPayment->items as $it) {
                    $amount = (float)($it->amount ?? (($it->unit_price ?? 0) * ($it->quantity ?? 1)));
                    $label = $it->product_name ?? '';
                    if (stripos((string)$label, 'fee') !== false || ($it->category ?? '') === 'other_charges') {
                        $transferFee += $amount;
                    }
                    $currentItems[] = [
                        'date' => $it->item_date ? (is_string($it->item_date) ? date('Y-m-d', strtotime($it->item_date)) : $it->item_date->format('Y-m-d')) : '',
                        'name' => $label,
                        'amount' => $amount,
                    ];
                }
            }
            // Consolidate duplicate names
            $currentItems = $this->consolidateDisplayItems($currentItems);

            // Build previous items
            $prevItems = [];
            $prevTransferFee = 0.0;
            if ($previousPayment) {
                foreach ($previousPayment->items as $it) {
                    $labelPrev = $it->product_name ?? '';
                    $amtPrev = (float)($it->amount ?? (($it->unit_price ?? 0) * ($it->quantity ?? 1)));
                    if (stripos((string)$labelPrev, 'fee') !== false || ($it->category ?? '') === 'other_charges') {
                        $prevTransferFee += $amtPrev;
                    }
                    $prevItems[] = [
                        'date' => $it->item_date ? (is_string($it->item_date) ? date('Y-m-d', strtotime($it->item_date)) : $it->item_date->format('Y-m-d')) : '',
                        'name' => $labelPrev,
                        'amount' => $amtPrev,
                    ];
                }
            }
            $prevItems = $this->consolidateDisplayItems($prevItems);

            // Compute dates
            $billingDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
            $scheduledDebit = \Carbon\Carbon::create($year, $month, 1)->addMonthNoOverflow()->day(10);

            $rows[] = [
                // Front (address)
                'recipient_name' => ($customer->name ?: ($customer->user_name ?? '-')),
                'customer_number' => $customer->customer_number,
                'address' => $addr,
                'postal_code' => $pc,

                // Back - invoice (current)
                'current_items' => $currentItems,
                'current_amount' => $currentPayment ? (float)$currentPayment->amount : 0.0,
                'current_billing_date' => $billingDate->format('Y-m-d'),
                'scheduled_debit_date' => $scheduledDebit->format('Y-m-d'),
                'transfer_fee' => $transferFee,
                'previous_transfer_fee' => $prevTransferFee,

                // Back - receipt (previous)
                'previous_items' => $prevItems,
                'previous_amount' => $previousPayment ? (float)$previousPayment->amount : 0.0,
                'receipt_date' => ($previousPayment && $previousPayment->payment_date)
                    ? ($previousPayment->payment_date instanceof \Carbon\Carbon
                        ? $previousPayment->payment_date->format('Y-m-d')
                        : date('Y-m-d', strtotime($previousPayment->payment_date)))
                    : null,

                // Context
                'year' => $year,
                'month' => $month,
                'previous_year' => $previousYear,
                'previous_month' => $previousMonth,
            ];
        }
        // If odd count, add a blank row to keep pairs
        if ((count($rows) % 2) === 1) {
            $rows[] = [];
        }
        return $rows;
    }


    private function buildPostcardPrintPdfData(int $month, int $year): array
    {
        $payments = Payment::with(['customer', 'items' => function ($q) {
            $q->orderBy('item_date');
        }])
            ->where('payment_month', $month)
            ->where('payment_year', $year)
            ->orderBy('payment_date')
            ->get();

        $result = [];
        foreach ($payments as $payment) {
            $items = [];
            $transferFee = 0.0;
            foreach ($payment->items as $it) {
                $amount = (float)($it->amount ?? (($it->unit_price ?? 0) * ($it->quantity ?? 1)));
                $label = $it->product_name ?? '';

                if (stripos((string)$label, 'fee') !== false || ($it->category ?? '') === 'other_charges') {
                    $transferFee += $amount;
                }
                $items[] = [
                    'date' => $it->item_date ? (is_string($it->item_date) ? date('n/j', strtotime($it->item_date)) : $it->item_date->format('n/j')) : '',
                    'name' => $label,
                    'amount' => $amount,
                ];
            }
            $items = $this->consolidateDisplayItems($items);

            $result[] = [
                'bill_title' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
                'recipient_name' => $payment->customer->name ?? '-',
                'customer_number' => $payment->customer->customer_number ?? '',
                'amount_total' => (float)$payment->amount,
                'transfer_date' => $payment->payment_date ? ($payment->payment_date instanceof \Carbon\Carbon ? $payment->payment_date->format('F j, Y') : date('F j, Y', strtotime($payment->payment_date))) : '',
                'items' => $items,
                'transfer_fee' => $transferFee,
            ];
        }
        return $result;
    }

    public function index(Request $request)
    {
        $query = Payment::with('customer');

        if ($request->filled('payment_month')) {
            $query->where('payment_month', $request->get('payment_month'));
        }

        if ($request->filled('payment_year')) {
            $query->where('payment_year', $request->get('payment_year'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20);
        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $customers = Customer::orderBy('user_name')->get();
        return view('payments.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_month' => 'required|integer|between:1,12',
            'payment_year' => 'required|integer|min:2020',
            'amount' => 'required|numeric|min:0|max:99999999.99',
            'payment_date' => 'required|date',
            'receipt_number' => 'nullable|string|max:50',
            'status' => 'required|in:pending,completed,failed',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.row_no' => 'nullable|integer|min:1',
            'items.*.item_date' => 'nullable|date',
            'items.*.product_code' => 'nullable|string|max:50',
            'items.*.product_name' => 'required_with:items|string|max:255',
            'items.*.unit_price' => 'nullable|numeric',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.amount' => 'nullable|numeric',
            'items.*.tax_rate' => 'nullable|numeric',
            'items.*.tax_amount' => 'nullable|numeric',
            'items.*.category' => 'nullable|string|max:50',
            'items.*.notes' => 'nullable|string',
            'other_payments' => 'nullable|array',
            'other_payments.*.row_no' => 'nullable|integer|min:1',
            'other_payments.*.item_date' => 'nullable|date',
            'other_payments.*.amount' => 'required_with:other_payments|numeric',
            'other_payments.*.notes' => 'nullable|string',
        ]);

        $payment = Payment::create($validated);

        $subtotal = 0.0;
        $taxTotal = 0.0;
        $otherFees = 0.0;
        if ($request->filled('items')) {
            foreach ($request->input('items') as $index => $item) {
                if (!isset($item['product_name']) || $item['product_name'] === '') {
                    continue;
                }
                $unitPrice = (float)($item['unit_price'] ?? 0);
                $quantity = (float)($item['quantity'] ?? 1);
                $amount = $item['amount'] !== null && $item['amount'] !== ''
                    ? (float)$item['amount']
                    : $unitPrice * $quantity;
                $taxRate = (float)($item['tax_rate'] ?? 0);
                $calculatedTax = (float)($item['tax_amount'] ?? round($amount * $taxRate / 100, 2));

                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'row_no' => $item['row_no'] ?? ($index + 1),
                    'item_date' => $item['item_date'] ?? null,
                    'product_code' => $item['product_code'] ?? null,
                    'product_name' => $item['product_name'],
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'amount' => $amount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $calculatedTax,
                    'category' => $item['category'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);

                $subtotal += $amount;
                $taxTotal += $calculatedTax;
                if (($item['category'] ?? '') === 'other_charges') {
                    $otherFees += $amount + $calculatedTax;
                }
            }
        }

        // Save その他入金 rows as PaymentItem with category 'other_payment'
        if ($request->filled('other_payments')) {
            foreach ($request->input('other_payments') as $index => $op) {
                if (!isset($op['amount']) || $op['amount'] === '') {
                    continue;
                }
                $amount = (float) $op['amount'];
                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'row_no' => $op['row_no'] ?? (1000 + $index + 1),
                    'item_date' => $op['item_date'] ?? null,
                    'product_code' => null,
                    'product_name' => 'その他入金',
                    'unit_price' => $amount,
                    'quantity' => 1,
                    'amount' => $amount,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'category' => 'other_payment',
                    'notes' => $op['notes'] ?? null,
                ]);
            }
        }

        $payment->update([
            'subtotal_amount' => $subtotal ?: null,
            'tax_total' => $taxTotal ?: null,
            'other_fees_total' => $otherFees ?: null,
            'grand_total' => ($subtotal + $taxTotal + $otherFees) ?: null,
        ]);

        return redirect()->route('payments.index')
            ->with('success', 'お支払いが成功しました。');
    }

    public function show(Payment $payment)
    {
        $payment->load(['customer', 'items' => function ($q) {
            $q->orderBy('row_no');
        }]);

        $items = $payment->items ?? collect();
        $grouped = [
            'current' => $items->filter(function ($it) {
                return ($it->category ?? '') === '' || $it->category === null;
            }),
            'previous' => $items->filter(function ($it) {
                return ($it->category ?? '') === 'previous_balance';
            }),
            'other' => $items->filter(function ($it) {
                return ($it->category ?? '') === 'other_charges';
            }),
            'notice' => $items->filter(function ($it) {
                return ($it->category ?? '') === 'notice';
            }),
        ];

        $sectionTotals = [
            'current' => $this->computeTotalsForItems($grouped['current']),
            'previous' => $this->computeTotalsForItems($grouped['previous']),
            'other' => $this->computeTotalsForItems($grouped['other']),
            'notice' => $this->computeTotalsForItems($grouped['notice']),
        ];

        return view('payments.show', compact('payment', 'grouped', 'sectionTotals'));
    }

    public function edit(Payment $payment)
    {
        $customers = Customer::orderBy('user_name')->get();
        return view('payments.edit', compact('payment', 'customers'));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_month' => 'required|integer|between:1,12',
            'payment_year' => 'required|integer|min:2020',
            // Ensure amount fits into DECIMAL(10,2)
            'amount' => 'required|numeric|min:0|max:99999999.99',
            'payment_date' => 'required|date',
            'receipt_number' => 'nullable|string|max:50',
            'status' => 'required|in:pending,completed,failed',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|integer|exists:payment_items,id',
            'items.*.row_no' => 'nullable|integer|min:1',
            'items.*.item_date' => 'nullable|date',
            'items.*.product_code' => 'nullable|string|max:50',
            'items.*.product_name' => 'required_with:items|string|max:255',
            'items.*.unit_price' => 'nullable|numeric',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.amount' => 'nullable|numeric',
            'items.*.tax_rate' => 'nullable|numeric',
            'items.*.tax_amount' => 'nullable|numeric',
            'items.*.category' => 'nullable|string|max:50',
            'items.*.notes' => 'nullable|string',
            'other_payments' => 'nullable|array',
            'other_payments.*.row_no' => 'nullable|integer|min:1',
            'other_payments.*.item_date' => 'nullable|date',
            'other_payments.*.amount' => 'required_with:other_payments|numeric',
            'other_payments.*.notes' => 'nullable|string',
        ]);

        $payment->update($validated);

        // Sync items: simple replace strategy for clarity
        $payment->items()->delete();
        $subtotal = 0.0;
        $taxTotal = 0.0;
        $otherFees = 0.0;
        if ($request->filled('items')) {
            foreach ($request->input('items') as $index => $item) {
                if (!isset($item['product_name']) || $item['product_name'] === '') {
                    continue;
                }
                $unitPrice = (float)($item['unit_price'] ?? 0);
                $quantity = (float)($item['quantity'] ?? 1);
                $amount = $item['amount'] !== null && $item['amount'] !== ''
                    ? (float)$item['amount']
                    : $unitPrice * $quantity;
                $taxRate = (float)($item['tax_rate'] ?? 0);
                $calculatedTax = (float)($item['tax_amount'] ?? round($amount * $taxRate / 100, 2));

                $payment->items()->create([
                    'row_no' => $item['row_no'] ?? ($index + 1),
                    'item_date' => $item['item_date'] ?? null,
                    'product_code' => $item['product_code'] ?? null,
                    'product_name' => $item['product_name'],
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'amount' => $amount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $calculatedTax,
                    'category' => $item['category'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);

                $subtotal += $amount;
                $taxTotal += $calculatedTax;
                if (($item['category'] ?? '') === 'other_charges') {
                    $otherFees += $amount + $calculatedTax;
                }
            }
        }

        // Re-create その他入金 rows
        if ($request->filled('other_payments')) {
            foreach ($request->input('other_payments') as $index => $op) {
                if (!isset($op['amount']) || $op['amount'] === '') {
                    continue;
                }
                $amount = (float) $op['amount'];
                $payment->items()->create([
                    'row_no' => $op['row_no'] ?? (1000 + $index + 1),
                    'item_date' => $op['item_date'] ?? null,
                    'product_code' => null,
                    'product_name' => 'その他入金',
                    'unit_price' => $amount,
                    'quantity' => 1,
                    'amount' => $amount,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'category' => 'other_payment',
                    'notes' => $op['notes'] ?? null,
                ]);
            }
        }

        $payment->update([
            'subtotal_amount' => $subtotal ?: null,
            'tax_total' => $taxTotal ?: null,
            'other_fees_total' => $otherFees ?: null,
            'grand_total' => ($subtotal + $taxTotal + $otherFees) ?: null,
        ]);

        return redirect()->route('payments.index')
            ->with('success', '支払いが正常に更新されました。');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return redirect()->route('payments.index')
            ->with('success', '支払いが正常に削除されました。');
    }

    public function showUploadForm()
    {

        $currentMonth = now()->month;
        $currentYear = now()->year;
        return view('payments.upload', compact('currentMonth', 'currentYear'));
    }

    public function showPostcardForm()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        return view('payments.postcard-form', compact('currentMonth', 'currentYear'));
    }

    public function uploadMonthEndData(Request $request)
    {

        $request->validate([
            'payment_file' => 'required|file|mimes:csv,txt,xlsx|max:2048', // 2MB max
            'payment_month' => 'required|integer|between:1,12',
            'payment_year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
        ], [
            'payment_file.required' => 'アップロードするCSVファイルを選択してください。',
            'payment_file.mimes' => 'ファイルはCSV、TXT、またはXLSX形式である必要があります。',
            'payment_file.max' => 'ファイルサイズは2MB以下である必要があります。',
            'payment_month.between' => '月は1から12の間で選択してください。',
            'payment_year.min' => '年は2020年以降を選択してください。',
            'payment_year.max' => '年は' . (date('Y') + 1) . '年以前を選択してください。',
        ]);

        try {
            $file = $request->file('payment_file');
            $path = $file->store('uploads');
            $extension = strtolower($file->getClientOriginalExtension());

            $data = [];

            if ($extension === 'xlsx') {

                $spreadsheet = IOFactory::load(storage_path('app/' . $path));
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                if (empty($rows) || count($rows) < 2) {
                    throw new \Exception('XLSXファイルが空であるか、データが不足しています。');
                }

                $header = array_shift($rows);

                // Detailed deposit layout? Import via detailed path
                if ($this->looksLikeDetailedDepositHeader($header)) {
                    [$imported, $errors] = $this->importDetailedDepositXlsx($rows, $header);

                    Storage::delete($path);

                    $message = "{$imported}件の入金データを正常に取り込みました。";
                    if (!empty($errors)) {
                        $message .= " エラー: " . implode('; ', array_slice($errors, 0, 10));
                        if (count($errors) > 10) {
                            $message .= " (他" . (count($errors) - 10) . "件のエラー)";
                        }
                    }

                    $alertType = !empty($errors) ? 'warning' : 'success';
                    return redirect()->route('payments.index')
                        ->with($alertType, $message);
                }

                // Simple format fallback: 顧客番号, 金額, 入金日, 受付番号(任意)
                if (!$header || count($header) < 3) {
                    throw new \Exception('XLSXフォーマットが無効です。最低3列必要です：顧客番号、金額、入金日。');
                }

                $data = $rows;
            } else {

                $fileContent = file_get_contents(storage_path('app/' . $path));
                $encoding = mb_detect_encoding($fileContent, ['UTF-8', 'SJIS', 'EUC-JP'], true);
                if ($encoding && $encoding !== 'UTF-8') {
                    $fileContent = mb_convert_encoding($fileContent, 'UTF-8', $encoding);
                    file_put_contents(storage_path('app/' . $path), $fileContent);
                }

                $handle = fopen(storage_path('app/' . $path), 'r');

                if (!$handle) {
                    throw new \Exception('アップロードされたファイルを読み込めません。');
                }
                $header = fgetcsv($handle);
                if (!$header || count($header) < 3) {
                    throw new \Exception('CSVフォーマットが無効です。最低3列必要です：顧客番号、金額、入金日。');
                }
                while (($row = fgetcsv($handle)) !== false) {
                    if (!empty(array_filter($row))) {
                        $data[] = $row;
                    }
                }
                fclose($handle);
            }

            $imported = 0;
            $errors = [];
            $rowNumber = 1;

            $customerCount = Customer::count();
            Log::info('XLSX Import Debug', [
                'extension' => $extension,
                'data_count' => count($data),
                'customer_count_in_db' => $customerCount,
                'first_few_rows' => array_slice($data, 0, 3)
            ]);

            DB::beginTransaction();

            try {
                foreach ($data as $rowData) {
                    $rowNumber++;

                    if (empty(array_filter($rowData))) {
                        continue;
                    }

                    if (empty($rowData[0])) {
                        $errors[] = "{$rowNumber}行目: 顧客番号が必要です。";
                        continue;
                    }

                    $rawAmount = isset($rowData[1]) ? preg_replace('/[^0-9.\-]/', '', (string) $rowData[1]) : '';
                    if ($rawAmount === '' || !is_numeric($rawAmount)) {
                        $errors[] = "{$rowNumber}行目: 有効な金額が必要です。";
                        continue;
                    }
                    $amount = (float) $rawAmount;
                    if ($amount < 0 || $amount > 99999999.99) {
                        $errors[] = "{$rowNumber}行目: 金額が許可範囲外です。";
                        continue;
                    }

                    $customer = Customer::where('customer_number', trim($rowData[0]))->first();

                    if (!$customer) {
                        $errors[] = "{$rowNumber}行目: 顧客番号'{$rowData[0]}'が見つかりません。";
                        continue;
                    }

                    $paymentDate = null;
                    if (!empty($rowData[2])) {
                        try {
                            $paymentDate = Carbon::parse($rowData[2]);
                        } catch (\Exception $e) {
                            $paymentDate = now();
                        }
                    } else {
                        $paymentDate = now();
                    }


                    $receiptNumber = isset($rowData[3]) ? trim((string)$rowData[3]) : null;

                    if ($receiptNumber !== null && $receiptNumber !== '') {
                        // Upsert by receipt_number when provided
                        $payment = Payment::where('receipt_number', $receiptNumber)->first();
                        if ($payment) {
                            $payment->update([
                                'customer_id' => $customer->id,
                                'payment_month' => $request->payment_month,
                                'payment_year' => $request->payment_year,
                                'amount' => $amount,
                                'payment_date' => $paymentDate,
                                'status' => 'completed',
                                'notes' => '月末データから取込(上書き)'
                            ]);
                        } else {
                            Payment::create([
                                'customer_id' => $customer->id,
                                'payment_month' => $request->payment_month,
                                'payment_year' => $request->payment_year,
                                'amount' => $amount,
                                'payment_date' => $paymentDate,
                                'receipt_number' => $receiptNumber,
                                'status' => 'completed',
                                'notes' => '月末データから取込',
                            ]);
                        }
                    } else {
                        // Fallback to month/year duplicate detection when no receipt number is provided
                        $existingPayment = Payment::where('customer_id', $customer->id)
                            ->where('payment_month', $request->payment_month)
                            ->where('payment_year', $request->payment_year)
                            ->first();
                        if ($existingPayment) {
                            $errors[] = "{$rowNumber}行目: 顧客'{$customer->name}'の{$request->payment_year}年{$request->payment_month}月の入金が既に存在します。";
                            continue;
                        }
                        Payment::create([
                            'customer_id' => $customer->id,
                            'payment_month' => $request->payment_month,
                            'payment_year' => $request->payment_year,
                            'amount' => $amount,
                            'payment_date' => $paymentDate,
                            'receipt_number' => null,
                            'status' => 'completed',
                            'notes' => '月末データから取込',
                        ]);
                    }

                    $imported++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            Storage::delete($path);

            Log::info('XLSX Import Results', [
                'imported' => $imported,
                'error_count' => count($errors),
                'errors' => array_slice($errors, 0, 5)
            ]);

            $message = "{$imported}件の入金データを正常に取り込みました。";
            if (!empty($errors)) {
                $message .= " エラー: " . implode('; ', array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $message .= " (他" . (count($errors) - 10) . "件のエラー)";
                }
            }

            $alertType = !empty($errors) ? 'warning' : 'success';

            return redirect()->route('payments.index')
                ->with($alertType, $message);
        } catch (\Exception $e) {

            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['payment_file' => 'アップロードに失敗しました: ' . $e->getMessage()]);
        }
    }

    public function generatePostcardData(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);
        $currentMonth = (int) $request->month;
        $currentYear = (int) $request->year;
        $postcardData = $this->buildPostcardData($currentMonth, $currentYear);
        $postcardData = array_map(function (array $row) {
            return [
                '顧客' => $row['customer'] ?? null,
                '当月名' => $row['current_month_name'] ?? null,
                '当月入金' => $row['current_payment'] ?? null,
                '前月名' => $row['previous_month_name'] ?? null,
                '前月入金' => $row['previous_payment'] ?? null,
            ];
        }, $postcardData);
        // Enforce 20-per-page pagination using offset/limit
        $total = count($postcardData);
        $limit = 20; // fixed page size as requested
        $offset = max(0, (int) $request->get('offset', 0));
        if ($offset >= $total) {
            $offset = max(0, $total > 0 ? ($total - ($total % $limit ?: $limit)) : 0);
        }

        $postcardData = array_slice($postcardData, $offset, $limit);

        return view('payments.postcard-data', compact('postcardData', 'currentMonth', 'currentYear', 'total', 'offset', 'limit'));
    }

    public function exportPostcardCsv(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year');
        $currentMonth = (int) $month;
        $currentYear = (int) $year;
        $postcardData = $this->buildPostcardData($currentMonth, $currentYear);
        $filename = "はがき_{$year}_{$month}_" . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($postcardData, $currentMonth, $currentYear) {
            echo "\xEF\xBB\xBF";
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                '顧客名',
                '顧客番号',
                '住所',
                '郵便番号',
                '当月',
                '当月の決済額',
                '当月決済日',
                '前月',
                '以前の領収書番号',
                '以前のお支払い額'
            ]);

            foreach ($postcardData as $row) {
                fputcsv($file, [
                    $row['customer']->name,
                    $row['customer']->customer_number,
                    $row['customer']->address,
                    $row['customer']->postal_code,
                    $row['current_month_name'],
                    $row['current_payment'] ? $row['current_payment']->amount : 0,
                    $row['current_payment'] && $row['current_payment']->payment_date ? $row['current_payment']->payment_date->format('Y-m-d') : '',
                    $row['previous_month_name'],
                    $row['previous_payment'] ? $row['previous_payment']->receipt_number : '',
                    $row['previous_payment'] ? $row['previous_payment']->amount : 0,
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportPostcardPdf(Request $request)
    {
        $month = (int) $request->get('month');
        $year = (int) $request->get('year');
        $postcardData = $this->buildPostcardData($month, $year);
        $postcardData = array_map(function (array $row) {
            return [
                '顧客' => $row['customer'] ?? null,
                '当月名' => $row['current_month_name'] ?? null,
                '当月入金' => $row['current_payment'] ?? null,
                '前月名' => $row['previous_month_name'] ?? null,
                '前月入金' => $row['previous_payment'] ?? null,
            ];
        }, $postcardData);
        $embeddedFontBase64 = $this->loadJapaneseFontBase64();
        PDF::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'NotoSansJP',
            'dpi' => 96,
            'fontDir' => base_path('resources/fonts'),
            'fontCache' => storage_path('fonts'),
        ]);

        try {
            $pdf = PDF::loadView('postcards.pdf', [
                'postcardData' => $postcardData,
            ])->setPaper('a4');
            $filename = sprintf('はがき_%04d_%02d_%s.pdf', (int)$year, (int)$month, date('Y-m-d_H-i-s'));
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate PDF'], 500);
        }
    }

    public function exportPostcardPrintCsv(Request $request)
    {
        $month = (int) $request->get('month');
        $year = (int) $request->get('year');
        if (!$month || $month < 1 || $month > 12 || !$year || $year < 2020) {
            return redirect()->back()->with('error', '有効な月と年を選択してください。');
        }
        $data = $this->generatePostcardPrintData($month, $year);
        $filename = "postcard_print_{$year}_{$month}_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        $callback = function () use ($data) {
            echo "\xEF\xBB\xBF";
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Recipient Name',
                'Customer Number',
                'Address',
                'Postal Code',
                'Current Month',
                'Current Year',
                'Current Amount',
                'Current Payment Date',
                'Current Receipt Number',
                'Previous Month',
                'Previous Year',
                'Previous Amount',
                'Previous Receipt Number'
            ]);
            foreach ($data as $row) {
                fputcsv($file, [
                    $row['recipient_name'],
                    $row['customer_number'],
                    $row['address'],
                    $row['postal_code'],
                    $row['current_month'],
                    $row['current_year'],
                    $row['current_amount'],
                    $row['current_payment_date'],
                    $row['current_receipt_number'],
                    $row['previous_month'],
                    $row['previous_year'],
                    $row['previous_amount'],
                    $row['previous_receipt_number']
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportPostcardPrintPdf(Request $request)
    {

        try {
            @set_time_limit(180);
        } catch (\Throwable $e) {
        }
        try {
            @ini_set('max_execution_time', '180');
        } catch (\Throwable $e) {
        }
        try {
            @ini_set('memory_limit', '512M');
        } catch (\Throwable $e) {
        }

        $paymentId = $request->get('payment_id');
        if ($paymentId) {
            $payment = Payment::with(['customer', 'items' => function ($q) {
                $q->orderBy('item_date');
            }])->find($paymentId);
            if (!$payment) {
                return redirect()->back()->with('error', '指定された入金が見つかりませんでした。');
            }
            $month = (int) ($payment->payment_month ?? now()->month);
            $year = (int) ($payment->payment_year ?? now()->year);
            // Current items and totals
            $currentItems = [];
            $transferFee = 0.0;
            foreach ($payment->items as $it) {
                $amount = (float)($it->amount ?? (($it->unit_price ?? 0) * ($it->quantity ?? 1)));
                $label = $it->product_name ?? '';
                if (stripos((string)$label, 'fee') !== false || ($it->category ?? '') === 'other_charges') {
                    $transferFee += $amount;
                }
                $currentItems[] = [
                    'date' => $it->item_date ? (is_string($it->item_date) ? date('Y-m-d', strtotime($it->item_date)) : $it->item_date->format('Y-m-d')) : '',
                    'name' => $label,
                    'amount' => $amount,
                ];
            }

            // Previous month for this customer
            $prevMonth = $month === 1 ? 12 : $month - 1;
            $prevYear  = $month === 1 ? $year - 1 : $year;
            $prev = Payment::with(['items'])->where('customer_id', $payment->customer_id)
                ->where('payment_month', $prevMonth)
                ->where('payment_year', $prevYear)
                ->first();
            $prevItems = [];
            $receiptDate = null;
            $prevAmount = 0.0;
            if ($prev) {
                $receiptDate = $prev->payment_date ? ($prev->payment_date instanceof \Carbon\Carbon ? $prev->payment_date->format('Y-m-d') : date('Y-m-d', strtotime($prev->payment_date))) : null;
                $prevAmount = (float)$prev->amount;
                foreach ($prev->items as $pit) {
                    $prevItems[] = [
                        'date' => $pit->item_date ? (is_string($pit->item_date) ? date('Y-m-d', strtotime($pit->item_date)) : $pit->item_date->format('Y-m-d')) : '',
                        'name' => $pit->product_name ?? '',
                        'amount' => (float)($pit->amount ?? (($pit->unit_price ?? 0) * ($pit->quantity ?? 1))),
                    ];
                }
                $prevItems = $this->consolidateDisplayItems($prevItems);
            }

            $billingDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
            $scheduledDebit = \Carbon\Carbon::create($year, $month, 1)->addMonthNoOverflow()->day(10)->format('Y-m-d');

            // Resolve postal code and address reliably from the payment's customer
            [$pc, $addr] = $payment && $payment->customer ? $this->buildCustomerAddress($payment->customer) : ['', ''];

            $data = [[
                'recipient_name' => (($payment->customer->name ?? '') !== '' ? $payment->customer->name : ($payment->customer->user_name ?? '-')),
                'customer_number' => $payment->customer->customer_number ?? '',
                'address' => $addr,
                'postal_code' => $pc,
                'year' => $year,
                'month' => $month,
                'current_items' => $currentItems,
                'current_amount' => (float)$payment->amount,
                'current_billing_date' => $billingDate,
                'scheduled_debit_date' => $scheduledDebit,
                'transfer_fee' => $transferFee,
                'previous_year' => $prevYear,
                'previous_month' => $prevMonth,
                'previous_items' => $prevItems,
                'previous_amount' => $prevAmount,
                'receipt_date' => $receiptDate,
            ]];
        } else {
            $month = (int) $request->get('month');
            $year = (int) $request->get('year');
            if (!$month || $month < 1 || $month > 12 || !$year || $year < 2020) {
                return redirect()->back()->with('error', '有効な月と年を選択してください。');
            }
            // Paginate customers to avoid timeouts
            $offset = (int) $request->get('offset', 0);
            // Keep default small to avoid 30s gateway timeout
            $limit = (int) $request->get('limit', 50);
            $data = $this->generatePostcardPrintData($month, $year, $offset, $limit);
            if (empty($data)) {
                $data = [[]];
            }
        }

        PDF::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => env('DOMPDF_DEFAULT_FONT', 'NotoSansJP'),
            'dpi' => config('dompdf.dpi', 96),
            'fontDir' => resource_path('fonts'),
            'fontCache' => storage_path('fonts'),
        ]);

        $pdf = PDF::loadView('postcards.print-pdf', ['data' => $data, 'month' => $month, 'year' => $year])
            ->setPaper('a4');
        $filename = sprintf('はがき印刷_%04d_%02d_%s_%d-%d.pdf', (int)$year, (int)$month, date('Y-m-d_H-i-s'), (int)($request->get('offset', 0)), (int)($request->get('limit', 50)));
        return $pdf->download($filename);
    }

    public function exportPostcardXlsx(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year');
        $currentMonth = (int) $month;
        $currentYear = (int) $year;
        $postcardData = $this->buildPostcardData($currentMonth, $currentYear);

        $filename = "はがき_{$year}_{$month}_" . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new PostcardExport($postcardData, $currentMonth, $currentYear), $filename);
    }

    public function exportPostcardPrintXlsx(Request $request)
    {
        $month = (int) $request->get('month');
        $year = (int) $request->get('year');
        if (!$month || $month < 1 || $month > 12 || !$year || $year < 2020) {
            return redirect()->back()->with('error', '有効な月と年を選択してください。');
        }
        $data = $this->generatePostcardPrintData($month, $year);
        $filename = "postcard_print_{$year}_{$month}_" . date('Y-m-d_H-i-s') . ".xlsx";

        // Build XLSX on the fly using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['Recipient Name', 'Customer Number', 'Address', 'Postal Code', 'Current Month', 'Current Year', 'Current Amount', 'Current Payment Date', 'Current Receipt Number', 'Previous Month', 'Previous Year', 'Previous Amount', 'Previous Receipt Number'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }
        $r = 2;
        foreach ($data as $row) {
            $sheet->fromArray([
                $row['recipient_name'] ?? '',
                $row['customer_number'] ?? '',
                $row['address'] ?? '',
                $row['postal_code'] ?? '',
                $row['current_month'] ?? '',
                $row['current_year'] ?? '',
                $row['current_amount'] ?? 0,
                $row['current_payment_date'] ?? '',
                $row['current_receipt_number'] ?? '',
                $row['previous_month'] ?? '',
                $row['previous_year'] ?? '',
                $row['previous_amount'] ?? 0,
                $row['previous_receipt_number'] ?? '',
            ], null, 'A' . $r);
            $r++;
        }
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'postcard_print_');
        $writer->save($tmp);
        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    public function showXlsxViewer()
    {
        return view('payments.xlsx-viewer');
    }

    public function showDetailedImportForm()
    {
        return view('payments.detailed-import');
    }

    // Preview detailed XLSX (re-add lightweight version here for UI)
    public function previewDetailedDepositXlsx(Request $request)
    {
        $request->validate([
            'xlsx_file' => 'required|file|mimes:xlsx|max:5120',
        ]);

        $file = $request->file('xlsx_file');
        $path = $file->store('temp');
        try {
            $spreadsheet = IOFactory::load(storage_path('app/' . $path));
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            if (empty($rows) || count($rows) < 2) {
                throw new \Exception('XLSXファイルが空です。');
            }
            $header = array_shift($rows);
            if (!$this->looksLikeDetailedHeader($header)) {
                throw new \Exception('詳細フォーマットを検出できません。見本の列名をご確認ください。');
            }

            $index = [];
            foreach ($header as $i => $h) {
                $index[trim((string)$h)] = $i;
            }
            $ym = $this->findHeaderIndex($index, ['対象年月', '年月', '対象月']);
            $code = $this->findHeaderIndex($index, ['顧客CD', '顧客番号']);
            $nameKana = $this->findHeaderIndex($index, ['氏名カナ', '利用者カナ氏名']);
            $name = $this->findHeaderIndex($index, ['氏名', '利用者氏名']);
            $edaban = $this->findHeaderIndex($index, ['枝番']);
            $product = $this->findHeaderIndex($index, ['商品名']);
            $qty = $this->findHeaderIndex($index, ['数量']);
            $unit = $this->findHeaderIndex($index, ['単価']);
            $amount = $this->findHeaderIndex($index, ['金額']);
            $tax = $this->findHeaderIndex($index, ['消費税', '税']);
            $payClass = $this->findHeaderIndex($index, ['支払区分']);
            $method = $this->findHeaderIndex($index, ['支払方法']);

            $data = [];
            foreach ($rows as $i => $r) {
                if (!empty(array_filter($r))) {
                    // Normalize display values to avoid locale/backslash issues
                    [$yy, $mm] = [null, null];
                    if ($ym !== null) {
                        [$yy, $mm] = $this->parseYearMonth((string)($r[$ym] ?? ''));
                    }
                    $dispYm = ($yy && $mm) ? sprintf('%04d/%02d', $yy, $mm) : (string)($r[$ym] ?? '');

                    $qtyVal = $this->parseDecimal($qty !== null ? ($r[$qty] ?? null) : null);
                    $unitVal = $this->parseDecimal($unit !== null ? ($r[$unit] ?? null) : null);
                    $amountVal = $this->parseDecimal($amount !== null ? ($r[$amount] ?? null) : null);
                    $taxVal = $this->parseDecimal($tax !== null ? ($r[$tax] ?? null) : null);

                    $data[] = [
                        'row' => $i + 2,
                        '対象年月' => $dispYm,
                        '顧客CD' => $code !== null ? ($r[$code] ?? '') : '',
                        '氏名カナ' => $nameKana !== null ? ($r[$nameKana] ?? '') : '',
                        '氏名' => $name !== null ? ($r[$name] ?? '') : '',
                        '枝番' => $edaban !== null ? ($r[$edaban] ?? '') : '',
                        '商品名' => $r[$product] ?? '',
                        '数量' => $qtyVal !== null ? (int)$qtyVal : (($qty !== null ? ($r[$qty] ?? '') : '')),
                        '単価' => $unitVal !== null ? $this->formatCurrency($unitVal) : (($unit !== null ? ($r[$unit] ?? '') : '')),
                        '金額' => $amountVal !== null ? $this->formatCurrency($amountVal) : ($r[$amount] ?? ''),
                        '消費税' => $taxVal !== null ? $this->formatCurrency($taxVal) : (($tax !== null ? ($r[$tax] ?? '') : '')),
                        '支払区分' => $payClass !== null ? ($r[$payClass] ?? '') : '',
                        '支払方法' => $method !== null ? ($r[$method] ?? '') : '',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'header' => $header,
                'rows' => $data,
                'filename' => $file->getClientOriginalName(),
                // Provide suggested default debit date based on next month 10th
                'suggested_debit_date' => \Carbon\Carbon::now()->startOfMonth()->addMonthNoOverflow()->day(10)->format('Y-m-d')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        } finally {
            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }
        }
    }

    // Commit import with batch id (from preview rows)
    public function commitDetailedDepositXlsx(Request $request)
    {
        $request->validate([
            'rows' => 'required|array|min:1',
            // Optional import options
            'debit_day' => 'nullable|integer|between:1,28',
            'debit_date' => 'nullable|date',
        ]);

        $rows = $request->input('rows');
        $batchId = 'B' . date('YmdHis') . '-' . substr(sha1(json_encode($rows) . microtime(true)), 0, 8);
        $errors = [];
        $imported = 0;

        // Group rows by (customer, year, month) and prepare for upsert
        $groups = [];
        foreach ($rows as $row) {
            $name = trim((string)($row['氏名'] ?? ''));
            $code = trim((string)($row['顧客CD'] ?? ''));
            $customer = null;
            if ($name !== '') {
                $customer = Customer::where('user_name', $name)->first();
                if (!$customer) {
                    $customer = Customer::where('user_name', 'like', '%' . $name . '%')->first();
                }
            }
            if (!$customer && $code !== '') {
                $customer = Customer::where('customer_number', $code)->orWhere('customer_code', $code)->first();
            }
            if (!$customer) {
                $errors[] = ($row['row'] ?? '?') . "行目: 顧客 '" . ($name ?: $code) . "' が見つかりません。";
                continue;
            }

            [$year, $month] = $this->parseYearMonth((string)($row['対象年月'] ?? ''));
            if (!$year || !$month) {
                $errors[] = ($row['row'] ?? '?') . '行目: 対象年月を解析できません。';
                continue;
            }

            $key = $customer->id . '|' . $year . '|' . $month;
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'customer' => $customer,
                    'year' => $year,
                    'month' => $month,
                    // Deduplicate items by normalized product name within the same customer-month
                    'items_by_key' => [],
                ];
            }
            if (!empty($row['商品名'])) {
                $normalizedName = mb_strtolower(preg_replace('/\s+/u', '', (string)$row['商品名']));
                $groups[$key]['items_by_key'][$normalizedName] = [
                    'row_no' => (int)($row['枝番'] ?? ($row['row'] ?? 0)),
                    'product_name' => (string)$row['商品名'],
                    'unit_price' => $this->parseDecimal($row['単価'] ?? null),
                    'quantity' => $this->parseDecimal($row['数量'] ?? null) ?? 1,
                    'amount' => $this->parseDecimal($row['金額'] ?? null) ?? 0,
                    'tax_amount' => $this->parseDecimal($row['消費税'] ?? null) ?? 0,
                    'category' => !empty($row['支払区分']) ? (string)$row['支払区分'] : null,
                    'notes' => trim((!empty($row['支払方法']) ? ('支払方法: ' . $row['支払方法']) : '')) ?: null,
                ];
            }
        }

        DB::beginTransaction();
        try {
            foreach ($groups as $group) {
                $customer = $group['customer'];
                $year = $group['year'];
                $month = $group['month'];
                $items = array_values($group['items_by_key']);
                $subtotal = 0.0;
                $tax = 0.0;
                foreach ($items as $it) {
                    $subtotal += (float)($it['amount'] ?? 0);
                    $tax += (float)($it['tax_amount'] ?? 0);
                }
                $grand = $subtotal + $tax;

                // Overwrite existing month's payment for this customer (idempotent import)
                $payment = Payment::where('customer_id', $customer->id)
                    ->where('payment_year', $year)
                    ->where('payment_month', $month)
                    ->first();

                // Do NOT set payment_date at capture commit time; keep it null until results import
                $paymentDate = null;

                if ($payment) {
                    // Replace totals and items to avoid duplication when importing multiple times
                    $payment->update([
                        'amount' => $grand,
                        'subtotal_amount' => $subtotal ?: null,
                        'tax_total' => $tax ?: null,
                        'grand_total' => $grand ?: null,
                        'payment_date' => $paymentDate,
                        'status' => 'pending',
                        'notes' => '詳細XLSXから取込(上書き)'
                    ]);
                    // Remove existing items for this payment and re-insert
                    $payment->items()->delete();
                } else {
                    $payment = Payment::create([
                        'customer_id' => $customer->id,
                        'payment_month' => $month,
                        'payment_year' => $year,
                        'amount' => $grand,
                        'subtotal_amount' => $subtotal ?: null,
                        'tax_total' => $tax ?: null,
                        'grand_total' => $grand ?: null,
                        'payment_date' => $paymentDate,
                        'status' => 'pending',
                        'notes' => '詳細XLSXから取込',
                        'import_batch_id' => $batchId,
                    ]);
                }

                foreach ($items as $it) {
                    PaymentItem::create([
                        'payment_id' => $payment->id,
                        'row_no' => $it['row_no'],
                        'item_date' => null,
                        'product_code' => null,
                        'product_name' => $it['product_name'],
                        'unit_price' => $it['unit_price'],
                        'quantity' => $it['quantity'],
                        'amount' => $it['amount'],
                        'tax_rate' => null,
                        'tax_amount' => $it['tax_amount'],
                        'category' => $it['category'],
                        'notes' => $it['notes'],
                    ]);
                }

                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $errors[] = 'エラー: ' . $e->getMessage();
        }

        return response()->json([
            'success' => empty($errors),
            'imported' => $imported,
            'batch_id' => $batchId,
            'errors' => $errors,
        ], empty($errors) ? 200 : 400);
    }

    public function deleteImportBatch(string $batchId)
    {
        $count = Payment::where('import_batch_id', $batchId)->count();
        Payment::where('import_batch_id', $batchId)->delete();
        return redirect()->back()->with('success', "バッチ {$batchId} の入金を {$count} 件削除しました。");
    }

    private function looksLikeDetailedHeader($header): bool
    {
        if (!is_array($header)) {
            return false;
        }
        $joined = implode('|', array_map(fn($h) => trim((string)$h), $header));
        foreach (['対象年月', '顧客', '商品名'] as $kw) {
            if (mb_strpos($joined, $kw) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Resolve scheduled debit date for a given year/month with options:
     * - options['debit_date'] => explicit Y-m-d overrides
     * - options['debit_day'] => day of month (1-28), else default to 10th of next month
     */
    private function resolveScheduledPaymentDate(int $year, int $month, array $options = []): \Carbon\Carbon
    {
        try {
            if (!empty($options['debit_date'])) {
                return \Carbon\Carbon::parse($options['debit_date']);
            }
        } catch (\Throwable $e) {
        }

        $day = (int)($options['debit_day'] ?? 10);
        if ($day < 1 || $day > 28) {
            $day = 10;
        }

        // Default: next month on selected day
        $base = \Carbon\Carbon::create($year, $month, 1)->addMonthNoOverflow();
        return $base->day($day);
    }
    public function previewXlsxData(Request $request)
    {
        $request->validate([
            'xlsx_file' => 'required|file|mimes:xlsx|max:5120', // 5MB max for viewer
        ], [
            'xlsx_file.required' => 'XLSXファイルを選択してください。',
            'xlsx_file.mimes' => 'ファイルはXLSX形式である必要があります。',
            'xlsx_file.max' => 'ファイルサイズは5MB以下である必要があります。',
        ]);

        try {
            $file = $request->file('xlsx_file');
            $path = $file->store('temp');

            // Load XLSX file
            $spreadsheet = IOFactory::load(storage_path('app/' . $path));
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                throw new \Exception('XLSXファイルが空です。');
            }

            $header = array_shift($rows); // Remove header row
            $data = [];

            foreach ($rows as $index => $row) {
                if (!empty(array_filter($row))) {
                    $data[] = [
                        'index' => $index + 2, // +2 because we removed header and arrays are 0-indexed
                        'customer_number' => $row[0] ?? '',
                        'amount' => $row[1] ?? '',
                        'payment_date' => $row[2] ?? '',
                        'receipt_number' => $row[3] ?? '',
                        'raw_data' => $row
                    ];
                }
            }

            // Clean up temp file
            Storage::delete($path);

            return response()->json([
                'success' => true,
                'header' => $header,
                'data' => $data,
                'total_rows' => count($data),
                'filename' => $file->getClientOriginalName()
            ]);
        } catch (\Exception $e) {
            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }
            return response()->json([
                'success' => false,
                'error' => 'ファイル処理エラー: ' . $e->getMessage()
            ], 400);
        }
    }

    public function importSelectedXlsxData(Request $request)
    {
        $request->validate([
            'payment_month' => 'required|integer|between:1,12',
            'payment_year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'selected_rows' => 'required|array|min:1',
            'selected_rows.*' => 'required|array',
        ], [
            'payment_month.between' => '月は1から12の間で選択してください。',
            'payment_year.min' => '年は2020年以降を選択してください。',
            'payment_year.max' => '年は' . (date('Y') + 1) . '年以前を選択してください。',
            'selected_rows.required' => '取り込むデータを選択してください。',
            'selected_rows.min' => '少なくとも1行のデータを選択してください。',
        ]);

        try {
            $imported = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($request->selected_rows as $rowIndex => $rowData) {
                $displayRow = $rowIndex + 1;

                // Validate required fields
                if (empty($rowData[0])) {
                    $errors[] = "{$displayRow}行目: 顧客番号が必要です。";
                    continue;
                }

                $rawAmount = isset($rowData[1]) ? preg_replace('/[^0-9.\-]/', '', (string) $rowData[1]) : '';
                if ($rawAmount === '' || !is_numeric($rawAmount)) {
                    $errors[] = "{$displayRow}行目: 有効な金額が必要です。";
                    continue;
                }
                $amount = (float) $rawAmount;

                if ($amount < 0 || $amount > 99999999.99) {
                    $errors[] = "{$displayRow}行目: 金額が許可範囲外です。";
                    continue;
                }

                $customer = Customer::where('customer_number', trim($rowData[0]))->first();
                if (!$customer) {
                    $errors[] = "{$displayRow}行目: 顧客番号'{$rowData[0]}'が見つかりません。";
                    continue;
                }

                $paymentDate = null;
                if (!empty($rowData[2])) {
                    try {
                        $paymentDate = Carbon::parse($rowData[2]);
                    } catch (\Exception $e) {
                        $paymentDate = now();
                    }
                } else {
                    $paymentDate = now();
                }


                $receiptNumber = isset($rowData[3]) ? trim((string)$rowData[3]) : null;

                if ($receiptNumber !== null && $receiptNumber !== '') {
                    // Upsert by receipt_number when provided
                    $payment = Payment::where('receipt_number', $receiptNumber)->first();
                    if ($payment) {
                        $payment -> update([
                            'customer_id' => $customer->id,
                            'payment_month' => $request->payment_month,
                            'payment_year' => $request->payment_year,
                            'amount' => $amount,
                            'payment_date' => $paymentDate,
                            'status' => 'completed',
                            'notes' => 'XLSXビューアーから取込(上書き)',
                        ]);
                    } else {
                        Payment::create([
                            'customer_id' => $customer->id,
                            'payment_month' => $request->payment_month,
                            'payment_year' => $request->payment_year,
                            'amount' => $amount,
                            'payment_date' => $paymentDate,
                            'receipt_number' => $receiptNumber,
                            'status' => 'completed',
                            'notes' => 'XLSXビューアーから取込',
                        ]);
                    }
                } else {
                    // Fallback duplicate guard by month/year if no receipt number present
                    $existingPayment = Payment::where('customer_id', $customer->id)
                        ->where('payment_month', $request->payment_month)
                        ->where('payment_year', $request->payment_year)
                        ->first();
                    if ($existingPayment) {
                        $errors[] = "{$displayRow}行目: 顧客'{$customer->name}'の{$request->payment_year}年{$request->payment_month}月の入金が既に存在します。";
                        continue;
                    }
                    Payment::create([
                        'customer_id' => $customer->id,
                        'payment_month' => $request->payment_month,
                        'payment_year' => $request->payment_year,
                        'amount' => $amount,
                        'payment_date' => $paymentDate,
                        'receipt_number' => null,
                        'status' => 'completed',
                        'notes' => 'XLSXビューアーから取込',
                    ]);
                }

                $imported++;
            }

            DB::commit();

            $message = "{$imported}件の入金データを正常に取り込みました。";
            if (!empty($errors)) {
                $message .= " エラー: " . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " (他" . (count($errors) - 5) . "件のエラー)";
                }
            }

            $alertType = !empty($errors) ? 'warning' : 'success';

            return redirect()->route('payments.index')
                ->with($alertType, $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['import' => 'インポートに失敗しました: ' . $e->getMessage()]);
        }
    }
}
