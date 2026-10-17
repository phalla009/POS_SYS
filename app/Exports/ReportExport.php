<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $results;
    protected $type;

    public function __construct($results, $type)
    {
        $this->results = collect($results);
        $this->type    = $type;
    }

    public function collection()
    {
        $rows = $this->results->map(function ($row) {
            return match ($this->type) {
                'sales' => [
                    'Date'           => $row->date,
                    'Payment Method' => $row->payment_method ?? '-',
                    'Total Qty'      => $row->qty ?? 0,
                    'Total USD'      => number_format($row->total ?? 0, 2),
                    'Total KHR'      => number_format(($row->total ?? 0) * 4000, 0),
                ],
                'financial' => [
                    'Date'            => $row->date,
                    'Sold Qty'        => $row->sold_qty ?? 0,
                    'Total Sales'     => round($row->total_sales ?? 0, 2),
                    'Inventory Items' => $row->inventory_items ?? 0,
                    'Total Inventory' => round($row->total_inventory ?? 0, 2),
                    'Net'             => round($row->net ?? 0, 2),
                ],
                'inventory' => [
                    'Product'   => $row->name,
                    'Quantity'  => $row->quantity ?? $row->stock ?? 0,
                    'Price USD' => number_format($row->price ?? 0, 2),
                    'Total USD' => number_format(($row->quantity ?? $row->stock ?? 0) * ($row->price ?? 0), 2),
                ],
                'customer' => [
                    'Name'        => $row->name,
                    'Gender'      => ucfirst($row->gender),
                    'Total Qty'   => $row->total_qty ?? 0,
                    'Total Price' => number_format($row->total_price ?? 0, 2),
                    'Joined'      => $row->created_at,
                ],
                default => (array) $row,
            };
        });

        // Financial: add a Grand Total row at the bottom
        if ($this->type === 'financial') {
            $rows = $rows->concat([[
                'Date'            => 'Grand Total',
                'Sold Qty'        => $this->results->sum('sold_qty'),
                'Total Sales'     => round($this->results->sum('total_sales'), 2),
                'Inventory Items' => $this->results->sum('inventory_items'),
                'Total Inventory' => round($this->results->sum('total_inventory'), 2),
                'Net'             => round($this->results->sum('net'), 2),
            ]]);
        }

        return $rows->values();
    }

    public function headings(): array
    {
        return match ($this->type) {
            'sales'     => ['Date', 'Payment Method', 'Total Qty', 'Total (USD)', 'Total (KHR)'],
            'financial' => ['Date', 'Sold Qty', 'Total Sales (USD)', 'Inventory Items', 'Total Inventory (USD)', 'Net (USD)'],
            'inventory' => ['Product', 'Quantity', 'Price (USD)', 'Total (USD)'],
            'customer'  => ['Name', 'Gender', 'Total Qty', 'Total Price', 'Joined'],
            default     => [],
        };
    }

    public function title(): string
    {
        return ucfirst($this->type) . ' Report';
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1A73E8']],
            ],
        ];

        // Financial: make the Grand Total row bold (header + rows + total)
        if ($this->type === 'financial') {
            $lastRow = $this->results->count() + 2;
            $styles[$lastRow] = ['font' => ['bold' => true]];
        }

        return $styles;
    }
}
