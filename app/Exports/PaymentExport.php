<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $payments;

    public function __construct($payments)
    {
        $this->payments = $payments;
    }

    public function collection()
    {
        return $this->payments;
    }

    public function headings(): array
    {
        return [
            '顧客番号',
            '顧客名',
            '支払い月',
            '支払い年',
            '金額',
            '支払い日',
            '領収書番号',
            'ステータス'
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->customer->customer_number,
            $payment->customer->name,
            $payment->payment_month,
            $payment->payment_year,
            $payment->amount,
            $payment->payment_date->format('Y-m-d'),
            $payment->receipt_number,
            $payment->status,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // 顧客番号
            'B' => 20, // 顧客名
            'C' => 12, // 支払い月
            'D' => 12, // 支払い年
            'E' => 15, // 金額
            'F' => 15, // 支払い日
            'G' => 20, // 領収書番号
            'H' => 15, // ステータス
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
            
            // Style the header row
            'A1:H1' => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => 'E6E6FA']
                ]
            ],
        ];
    }
}
