<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PostcardExport implements FromArray, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $postcardData;
    protected $currentMonth;
    protected $currentYear;

    public function __construct($postcardData, $currentMonth, $currentYear)
    {
        $this->postcardData = $postcardData;
        $this->currentMonth = $currentMonth;
        $this->currentYear = $currentYear;
    }

    public function array(): array
    {
        return $this->postcardData;
    }

    public function headings(): array
    {
        return [
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
        ];
    }

    public function map($row): array
    {
        return [
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
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // 顧客名
            'B' => 15, // 顧客番号
            'C' => 30, // 住所
            'D' => 12, // 郵便番号
            'E' => 15, // 当月
            'F' => 15, // 当月の決済額
            'G' => 15, // 当月決済日
            'H' => 15, // 前月
            'I' => 20, // 以前の領収書番号
            'J' => 15, // 以前のお支払い額
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
            
            // Style the header row
            'A1:J1' => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => 'E6E6FA']
                ]
            ],
        ];
    }
}
