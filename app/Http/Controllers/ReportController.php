<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Report;
use App\Models\InventoryItem;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $now   = Carbon::now();
        $start = $now->copy()->startOfMonth();
        $end   = $now->copy()->endOfMonth();

        $salesThisMonth  = Order::whereBetween('created_at', [$start, $end])
            ->sum('total_amount');

        $ordersThisMonth = Order::whereBetween('created_at', [$start, $end])
            ->count();

        // Total Inventory = value of stock bought this month (same as Financial report)
        $totalInventory  = InventoryItem::whereBetween('created_at', [$start, $end])
            ->sum('price');

        // Net = Sales - Inventory
        $net = $salesThisMonth - $totalInventory;

        $salesByDay = Order::selectRaw('DAY(created_at) as day, SUM(total_amount) as total_amount, SUM(quantity) as total_qty')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $chartLabels = $salesByDay->pluck('day');
        $chartAmount = $salesByDay->pluck('total_amount');
        $chartQty    = $salesByDay->pluck('total_qty');

        return view('reports.index', compact(
            'salesThisMonth',
            'ordersThisMonth',
            'totalInventory',
            'net',
            'chartLabels',
            'chartAmount',
            'chartQty'
        ));
    }

    public function show($id)
    {
        $report = Report::findOrFail($id);
        return view('reports.show', compact('report'));
    }

    // ─── Shared query logic ───────────────────────────────────────────────────
    /**
     * Build the [start, end] range from the From / To dates.
     * Start = 00:00:00 of From date, End = 23:59:59 of To date.
     */
    private function getDateRange($from, $to)
    {
        $start = $from
            ? Carbon::parse($from)->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $end = $to
            ? Carbon::parse($to)->endOfDay()
            : Carbon::now()->endOfDay();

        return [$start, $end];
    }

    /**
     * Validate type + From/To dates. On failure the caller redirects to the
     * report form (reports.index) instead of "back", because "back" can be
     * /reports/generate, which only accepts POST.
     */
    private function rangeValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'type'      => 'required|in:sales,financial,inventory,customer',
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ]);
    }

    /**
     * SQL that turns the value saved by the POS ('cash' / 'khqr')
     * into the label shown in reports.
     */
    private function paymentLabelSql(): string
    {
        return "CASE orders.payment_method
                    WHEN 'cash' THEN 'Cash'
                    WHEN 'khqr' THEN 'Bank'
                    ELSE '-'
                END";
    }

    private function getResults($type, $start, $end)
    {
        $label = $this->paymentLabelSql();

        if ($type === 'sales') {
            // One row per day PER payment method (Cash / Bank), as selected in POS.
            return DB::table('orders')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw("DATE(created_at) as date, $label as payment_method, SUM(quantity) as qty, SUM(total_amount) as total")
                ->groupByRaw("DATE(created_at), $label")
                ->orderBy('date')
                ->orderBy('payment_method')
                ->get();

        } elseif ($type === 'inventory') {
            $stockColumn = 'stock';
            $columns     = DB::getSchemaBuilder()->getColumnListing('products');

            if (!in_array($stockColumn, $columns)) {
                return DB::table('products')
                    ->select('name', 'price')
                    ->orderBy('name', 'asc')
                    ->get();
            }

            return DB::table('products')
                ->select(
                    'name',
                    $stockColumn,
                    'price',
                    DB::raw("$stockColumn * price as total")
                )
                ->orderBy($stockColumn, 'asc')
                ->get();

        } elseif ($type === 'customer') {
            return Customer::select('id', 'name', 'gender', 'created_at')
                ->withCount(['orders as total_qty' => function ($query) use ($start, $end) {
                    $query->whereBetween('order_date', [$start, $end])
                        ->select(DB::raw('SUM(quantity)'));
                }])
                ->withSum(['orders as total_price' => function ($query) use ($start, $end) {
                    $query->whereBetween('order_date', [$start, $end]);
                }], 'total_amount')
                ->get();

        } elseif ($type === 'financial') {
            // Total Sales: completed orders, grouped by day
            $sales = DB::table('orders')
                ->where('status', 'completed')
                ->whereBetween('order_date', [$start, $end])
                ->selectRaw('DATE(order_date) as date, SUM(quantity) as sold_qty, SUM(total_amount) as total_sales')
                ->groupByRaw('DATE(order_date)')
                ->get()
                ->keyBy(fn ($r) => (string) $r->date);

            // Total Inventory: stock bought, grouped by day
            $inventory = InventoryItem::whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as inventory_items, SUM(price) as total_inventory')
                ->groupByRaw('DATE(created_at)')
                ->get()
                ->keyBy(fn ($r) => (string) $r->date);

            return $sales->keys()
                ->merge($inventory->keys())
                ->unique()
                ->sort()
                ->values()
                ->map(function ($date) use ($sales, $inventory) {
                    $s = $sales->get($date);
                    $i = $inventory->get($date);

                    $totalSales     = (float) ($s->total_sales ?? 0);
                    $totalInventory = (float) ($i->total_inventory ?? 0);

                    return (object) [
                        'date'            => $date,
                        'sold_qty'        => (int) ($s->sold_qty ?? 0),
                        'total_sales'     => $totalSales,
                        'inventory_items' => (int) ($i->inventory_items ?? 0),
                        'total_inventory' => $totalInventory,
                        'net'             => $totalSales - $totalInventory,
                    ];
                });
        }

        return collect();
    }

    // ─── Generate (view) ──────────────────────────────────────────────────────
    public function generate(Request $request)
    {
        $validator = $this->rangeValidator($request);
        if ($validator->fails()) {
            return redirect()->route('reports.index')
                ->withErrors($validator)
                ->withInput();
        }

        $type = $request->input('type');
        $from = $request->input('from_date');
        $to   = $request->input('to_date');

        [$start, $end] = $this->getDateRange($from, $to);

        $results    = $this->getResults($type, $start, $end);
        $grandTotal = $type === 'sales' ? $results->sum('total') : 0;

        // Summary cards for the financial report
        $summary = null;
        if ($type === 'financial') {
            $totalSales     = $results->sum('total_sales');
            $totalInventory = $results->sum('total_inventory');

            $summary = [
                'sales'     => $totalSales,
                'inventory' => $totalInventory,
                'net'       => $totalSales - $totalInventory,
            ];
        }

        // Display label for views that still print $range (e.g. "2026-10-01 to 2026-10-21")
        $range = $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d');

        return view('reports.generated', compact(
            'type', 'range', 'from', 'to', 'results', 'start', 'end', 'grandTotal', 'summary'
        ));
    }

    // ─── Export to Excel ──────────────────────────────────────────────────────
    public function export(Request $request)
    {
        $validator = $this->rangeValidator($request);
        if ($validator->fails()) {
            return redirect()->route('reports.index')
                ->withErrors($validator)
                ->withInput();
        }

        $type = $request->input('type');

        [$start, $end] = $this->getDateRange(
            $request->input('from_date'),
            $request->input('to_date')
        );

        $results  = $this->getResults($type, $start, $end);
        $filename = ucfirst($type) . '_Report_' . $start->format('Y-m-d') . '_to_' . $end->format('Y-m-d') . '.xlsx';

        return Excel::download(new ReportExport($results, $type), $filename);
    }
}
