<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\ProductionLog;

class DashboardController extends Controller
{
    public function index()
    {
        // ===== Summary Stats =====
        $totalProducts = Product::count();

        // "Total Invoice" = number of SALES, not number of order rows. A POS
        // checkout with 3 cart items creates 3 `orders` rows sharing one
        // `pos_ref`, and those should count as 1 invoice, not 3. Orders
        // created via the normal "Add New Order" flow have pos_ref = null,
        // so each one is its own invoice (grouped by its own id). Mirrors
        // the same calculation used in OrderController::index().
        $totalInvoice = Order::get(['id', 'pos_ref'])
            ->unique(fn ($order) => $order->pos_ref ?? $order->id)
            ->count();

        $totalCustomers = Customer::count();
        $currentMonthRevenue = Order::whereMonth('order_date', now()->month)
                                     ->sum('total_amount');

        // ===== Recent Orders =====
        $recentOrders = Order::with(['customer', 'product'])
                             ->latest()
                             ->take(5)
                             ->get();

        // ===== Top 10 Best Selling Products =====
        $topProducts = Order::selectRaw('product_id, SUM(quantity) as total_sold')
                             ->with('product')
                             ->groupBy('product_id')
                             ->orderByDesc('total_sold')
                             ->take(10)
                             ->get();

        // ===== Monthly Sales Chart =====
        $monthlySales = Order::selectRaw('MONTH(order_date) as month_number, MONTHNAME(order_date) as month_name, SUM(total_amount) as total')
                             ->groupBy('month_number', 'month_name')
                             ->orderBy('month_number')
                             ->get();

        $months = $monthlySales->pluck('month_name')->toArray();
        $monthlyRevenue = $monthlySales->pluck('total')->toArray();

        // ===== Run Time vs Downtime Chart =====
        $productionLogs = ProductionLog::select('phase_name', 'run_time', 'downtime')->get();
        $productionPhases = $productionLogs->pluck('phase_name')->toArray();
        $runtime = $productionLogs->pluck('run_time')->toArray();
        $downtime = $productionLogs->pluck('downtime')->toArray();

        return view('index', compact(
            'totalProducts',
            'totalInvoice',
            'totalCustomers',
            'currentMonthRevenue',
            'recentOrders',
            'topProducts',
            'months',
            'monthlyRevenue',
            'productionPhases',
            'runtime',
            'downtime'
        ));
    }
}