<?php

namespace App\Http\Controllers;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PostcardExport;
use App\Exports\PostcardPrintExport;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

    private function generatePostcardPrintData(int $month, int $year): array
    {
        $previousMonth = $month === 1 ? 12 : $month - 1;
        $previousYear = $month === 1 ? $year - 1 : $year;

        $customers = Customer::with([
            'payments' => function ($query) use ($month, $year, $previousMonth, $previousYear) {
                $query->where(function ($q) use ($month, $year) {
                    $q->where('payment_month', $month)
                        ->where('payment_year', $year);
                })->orWhere(function ($q) use ($previousMonth, $previousYear) {
                    $q->where('payment_month', $previousMonth)
                        ->where('payment_year', $previousYear);
                });
            }
        ])->get();
        $rows = [];
        foreach ($customers as $customer) {
            $currentPayment = $customer->payments->where('payment_month', $month)
                ->where('payment_year', $year)
                ->first();
            $previousPayment = $customer->payments->where('payment_month', $previousMonth)
                ->where('payment_year', $previousYear)
                ->first();

            $rows[] = [
                'recipient_name' => $customer->name,
                'customer_number' => $customer->customer_number,
                'address' => $customer->address,
                'postal_code' => $customer->postal_code,
                'current_month' => $month,
                'current_year' => $year,
                'current_amount' => $currentPayment ? $currentPayment->amount : null,
                'current_payment_date' => ($currentPayment && $currentPayment->payment_date)
                    ? (is_string($currentPayment->payment_date)
                        ? $currentPayment->payment_date
                        : $currentPayment->payment_date->format('Y-m-d'))
                    : null,
                'current_receipt_number' => $currentPayment ? $currentPayment->receipt_number : null,
                'previous_month' => $previousMonth,
                'previous_year' => $previousYear,
                'previous_amount' => $previousPayment ? $previousPayment->amount : null,
                'previous_receipt_number' => $previousPayment ? $previousPayment->receipt_number : null,
            ];
        }
        return $rows;
    }

    /**
     * Build richer data for print-PDF including line items and transfer date.
     */
    private function buildPostcardPrintPdfData(int $month, int $year): array
    {
        $payments = Payment::with(['customer', 'items' => function($q){ $q->orderBy('item_date'); }])
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
                // Heuristic: capture transfer fee if product name looks like fee or marked as other charges
                if (stripos((string)$label, 'fee') !== false || ($it->category ?? '') === 'other_charges') {
                    $transferFee += $amount;
                }
                $items[] = [
                    'date' => $it->item_date ? (is_string($it->item_date) ? date('n/j', strtotime($it->item_date)) : $it->item_date->format('n/j')) : '',
                    'name' => $label,
                    'amount' => $amount,
                ];
            }

            $result[] = [
                'bill_title' => date('F Y', mktime(0,0,0,$month,1,$year)),
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
        $customers = Customer::orderBy('name')->get();
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
        ]);

        $payment = Payment::create($validated);
    
        $subtotal = 0.0; $taxTotal = 0.0; $otherFees = 0.0;
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
            'current' => $items->filter(function ($it) { return ($it->category ?? '') === '' || $it->category === null; }),
            'previous' => $items->filter(function ($it) { return ($it->category ?? '') === 'previous_balance'; }),
            'other' => $items->filter(function ($it) { return ($it->category ?? '') === 'other_charges'; }),
            'notice' => $items->filter(function ($it) { return ($it->category ?? '') === 'notice'; }),
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
        $customers = Customer::orderBy('name')->get();
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
        ]);

        $payment->update($validated);

        // Sync items: simple replace strategy for clarity
        $payment->items()->delete();
        $subtotal = 0.0; $taxTotal = 0.0; $otherFees = 0.0;
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
                if (!$header || count($header) < 3) {
                    throw new \Exception('XLSXフォーマットが無効です。最低3列必要です：顧客番号、金額、入金日。');
                }
                
                $data = $rows;
            } else {
                // Handle CSV/TXT files
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
            \Log::info('XLSX Import Debug', [
                'extension' => $extension,
                'data_count' => count($data),
                'customer_count_in_db' => $customerCount,
                'first_few_rows' => array_slice($data, 0, 3)
            ]);

            \DB::beginTransaction();
            
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
                        'receipt_number' => $rowData[3] ?? null,
                        'status' => 'completed',
                        'notes' => '月末データから取込',
                    ]);
                    
                    $imported++;
                }
                
                \DB::commit();
                
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }
            
            Storage::delete($path);
            
            \Log::info('XLSX Import Results', [
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
        return view('payments.postcard-data', compact('postcardData', 'currentMonth', 'currentYear'));
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

        $callback = function() use ($postcardData, $currentMonth, $currentYear) {
            echo "\xEF\xBB\xBF"; 
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                '顧客名', '顧客番号', '住所', '郵便番号',
                '当月', '当月の決済額', '当月決済日',
                '前月', '以前の領収書番号', '以前のお支払い額'
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
        \PDF::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'NotoSansJP', 
            'dpi' => 96,
            'fontDir' => base_path('resources/fonts'),
            'fontCache' => storage_path('fonts'),
            ]);
    
        try {       
            $pdf = \PDF::loadView('postcards.pdf', [
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
        $callback = function() use ($data) {
            echo "\xEF\xBB\xBF";
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Recipient Name', 'Customer Number', 'Address', 'Postal Code',
                'Current Month', 'Current Year', 'Current Amount', 'Current Payment Date', 'Current Receipt Number',
                'Previous Month', 'Previous Year', 'Previous Amount', 'Previous Receipt Number'
            ]);
            foreach ($data as $row) {
                fputcsv($file, [
                    $row['recipient_name'], $row['customer_number'], $row['address'], $row['postal_code'],
                    $row['current_month'], $row['current_year'], $row['current_amount'], $row['current_payment_date'], $row['current_receipt_number'],
                    $row['previous_month'], $row['previous_year'], $row['previous_amount'], $row['previous_receipt_number']
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);       
    }

    public function exportPostcardPrintPdf(Request $request)
    {
       
        $paymentId = $request->get('payment_id');
        if ($paymentId) {
            $payment = Payment::with(['customer', 'items' => function($q){ $q->orderBy('item_date'); }])->find($paymentId);
            if (!$payment) {
                return redirect()->back()->with('error', '指定された入金が見つかりませんでした。');
            }
            $month = (int) ($payment->payment_month ?? now()->month);
            $year = (int) ($payment->payment_year ?? now()->year);
            $rows = [];
            $transferFee = 0.0;
            foreach ($payment->items as $it) {
                $amount = (float)($it->amount ?? (($it->unit_price ?? 0) * ($it->quantity ?? 1)));
                $label = $it->product_name ?? '';
                if (stripos((string)$label, 'fee') !== false || ($it->category ?? '') === 'other_charges') {
                    $transferFee += $amount;
                }
                $rows[] = [
                    'date' => $it->item_date ? (is_string($it->item_date) ? date('n/j', strtotime($it->item_date)) : $it->item_date->format('n/j')) : '',
                    'name' => $label,
                    'amount' => $amount,
                ];
            }
            $data = [[
                'bill_title' => date('F Y', mktime(0,0,0,$month,1,$year)),
                'recipient_name' => $payment->customer->name ?? '-',
                'customer_number' => $payment->customer->customer_number ?? '',
                'amount_total' => (float)$payment->amount,
                'transfer_date' => $payment->payment_date ? ($payment->payment_date instanceof \Carbon\Carbon ? $payment->payment_date->format('F j, Y') : date('F j, Y', strtotime($payment->payment_date))) : '',
                'items' => $rows,
                'transfer_fee' => $transferFee,
            ]];
        } else {
            $month = (int) $request->get('month');
            $year = (int) $request->get('year');
            if (!$month || $month < 1 || $month > 12 || !$year || $year < 2020) {
                return redirect()->back()->with('error', '有効な月と年を選択してください。');
            }
            $data = $this->buildPostcardPrintPdfData($month, $year);
        }

        \PDF::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => env('DOMPDF_DEFAULT_FONT', 'NotoSansJP'),
            'dpi' => config('dompdf.dpi', 96),
            'fontDir' => resource_path('fonts'),
            'fontCache' => storage_path('fonts'),
        ]);

        $pdf = \PDF::loadView('postcards.print-pdf', [ 'data' => $data, 'month' => $month, 'year' => $year ])
            ->setPaper('a4');
        $filename = sprintf('はがき印刷_%04d_%02d_%s.pdf', (int)$year, (int)$month, date('Y-m-d_H-i-s'));
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
        
        return Excel::download(new PostcardPrintExport($data), $filename);
    }

    public function showXlsxViewer()
    {
        return view('payments.xlsx-viewer');
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
            
            \DB::beginTransaction();
            
            foreach ($request->selected_rows as $rowIndex => $rowData) {
                $displayRow = $rowIndex + 1;
                
                // Validate required fields
                if (empty($rowData[0])) {
                    $errors[] = "{$displayRow}行目: 顧客番号が必要です。";
                    continue;
                }
                
                // Validate amount
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
                
                // Find customer
                $customer = Customer::where('customer_number', trim($rowData[0]))->first();
                if (!$customer) {
                    $errors[] = "{$displayRow}行目: 顧客番号'{$rowData[0]}'が見つかりません。";
                    continue;
                }
                
                // Parse payment date
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
                
                // Check for existing payment
                $existingPayment = Payment::where('customer_id', $customer->id)
                    ->where('payment_month', $request->payment_month)
                    ->where('payment_year', $request->payment_year)
                    ->first();
                
                if ($existingPayment) {
                    $errors[] = "{$displayRow}行目: 顧客'{$customer->name}'の{$request->payment_year}年{$request->payment_month}月の入金が既に存在します。";
                    continue;
                }
                
                // Create payment record
                Payment::create([
                    'customer_id' => $customer->id,
                    'payment_month' => $request->payment_month,
                    'payment_year' => $request->payment_year,
                    'amount' => $amount,
                    'payment_date' => $paymentDate,
                    'receipt_number' => $rowData[3] ?? null,
                    'status' => 'completed',
                    'notes' => 'XLSXビューアーから取込',
                ]);
                
                $imported++;
            }
            
            \DB::commit();
            
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
            \DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['import' => 'インポートに失敗しました: ' . $e->getMessage()]);
        }
    }

   
}



