<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $customers;

    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    public function collection()
    {
        return $this->customers;
    }

    public function headings(): array
    {
        return [
            'お客様番号',
            '氏名',
            'ガーナ',
            '性別',
            '郵便番号',
            '住所',
            '電話番号',
            'メモ',
            '銀行名',
            '支店名',
            '口座名義',
            'ガーナ口座',
            '口座番号',
            '口座名義人',
            '銀行メモ'
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->customer_number,
            $customer->name,
            $customer->ghana,
            $customer->gender,
            $customer->postal_code,
            $customer->address,
            $customer->phone_number,
            $customer->note,
            $customer->bank_name,
            $customer->branch_name,
            $customer->account_name,
            $customer->account_ghana,
            $customer->account_number,
            $customer->account_holder,
            $customer->bank_note,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // お客様番号
            'B' => 20, // 氏名
            'C' => 15, // ガーナ
            'D' => 10, // 性別
            'E' => 12, // 郵便番号
            'F' => 30, // 住所
            'G' => 15, // 電話番号
            'H' => 20, // メモ
            'I' => 20, // 銀行名
            'J' => 20, // 支店名
            'K' => 20, // 口座名義
            'L' => 15, // ガーナ口座
            'M' => 15, // 口座番号
            'N' => 20, // 口座名義人
            'O' => 20, // 銀行メモ
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
            
            // Style the header row
            'A1:O1' => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => 'E6E6FA']
                ]
            ],
        ];
    }
}
