<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $results;
    protected $type;

    // USD → KHR exchange rate used in the sales report
    protected const KHR_RATE = 4000;

    public function __construct($results, $type)
    {
        $this->results = collect($results);
        $this->type    = $type;
    }

    /**
     * USD → KHR, rounded to the nearest 100 riel.
     * e.g. 40,520 → 40,500 and 498,960 → 499,000
     */
    private function toKhr($usd): int
    {
        return (int) (round(((float) $usd * self::KHR_RATE) / 100) * 100);
    }

    public function collection()
    {
        $rows = $this->results->map(function ($row) {
            return match ($this->type) {
                'sales' => [
                    'Date'           => $row->date,
                    'Payment Method' => $row->payment_method ?? '-',
                    'Total Qty'      => (int) ($row->qty ?? 0),
                    'Cash USD'       => null, // filled only in the Total row
                    'Cash KHR'       => null,
                    'Bank USD'       => null,
                    'Bank KHR'       => null,
                    'Total USD'      => round($row->total ?? 0, 2),
                    'Total KHR'      => $this->toKhr($row->total ?? 0),
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

        // Sales: add a Total row at the bottom
        if ($this->type === 'sales') {
            // Cash = payment method "Cash"; Bank = everything else, so Cash + Bank = Total
            $cash = $this->results->where('payment_method', 'Cash');
            $bank = $this->results->where('payment_method', '!=', 'Cash');

            // KHR is the sum of the rounded per-row values so every column adds up
            $khrSum = fn ($set) => $set->sum(fn ($r) => $this->toKhr($r->total ?? 0));

            $rows = $rows->concat([[
                'Date'           => '',
                'Payment Method' => 'Total',
                'Total Qty'      => (int) $this->results->sum('qty'),
                'Cash USD'       => round($cash->sum('total'), 2),
                'Cash KHR'       => $khrSum($cash),
                'Bank USD'       => round($bank->sum('total'), 2),
                'Bank KHR'       => $khrSum($bank),
                'Total USD'      => round($this->results->sum('total'), 2),
                'Total KHR'      => $khrSum($this->results),
            ]]);
        }

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
            'sales'     => ['Date', 'Payment Method', 'Total Qty', 'Cash(USD)', 'Cash(KHR)', 'Bank(USD)', 'Bank(KHR)', 'Total (USD)', 'Total (KHR)'],
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

        // Sales: style the Total row + number formats
        if ($this->type === 'sales') {
            $lastRow = $this->results->count() + 2; // header + rows + total

            // Header row spans A:I for sales
            unset($styles[1]);
            $styles['A1:I1'] = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1A73E8']],
            ];

            $styles["A{$lastRow}:I{$lastRow}"] = [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF9DB9E0']],
            ];

            $sheet->getStyle("B{$lastRow}")->getAlignment()->setHorizontal('right');

            // USD columns (D, F, H) and KHR columns (E, G, I)
            foreach (['D', 'F', 'H'] as $col) {
                $sheet->getStyle("{$col}2:{$col}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            }
            foreach (['E', 'G', 'I'] as $col) {
                $sheet->getStyle("{$col}2:{$col}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            }
        }

        // Financial: make the Grand Total row bold (header + rows + total)
        if ($this->type === 'financial') {
            $lastRow = $this->results->count() + 2;
            $styles[$lastRow] = ['font' => ['bold' => true]];
        }

        return $styles;
    }
}
