<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // ── Date range (same behaviour as the Orders page) ──────────────
        // Default = today. Clear both dates to see everything.
        if ($request->has('date_from') || $request->has('date_to')) {
            $dateFrom = $request->filled('date_from') ? $request->date_from : null;
            $dateTo   = $request->filled('date_to') ? $request->date_to : null;
        } else {
            $dateFrom = $dateTo = now()->format('Y-m-d');
        }

        $type   = $request->input('type');    // income | expense | null
        $method = $request->input('method');  // only applies to income
        $search = $request->input('search');

        $income  = collect();
        $expense = collect();

        // ── INCOME: completed orders ────────────────────────────────────
        if ($type !== 'expense') {
            $orders = Order::where('status', 'completed');

            if ($search) {
                $orders->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            }
            if ($method) {
                $orders->where('payment_method', $method);
            }
            if ($dateFrom) {
                $orders->whereDate('order_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $orders->whereDate('order_date', '<=', $dateTo);
            }

            $income = $orders->get()->map(fn ($o) => [
                'type'   => 'income',
                'amount' => (float) $o->total_amount,
                'day'    => Carbon::parse($o->order_date ?? $o->created_at)->format('Y-m-d'),
            ]);
        }

        // ── EXPENSE: inventory items (stock bought) ─────────────────────
        // Inventory has no payment method, so a method filter hides expenses.
        if ($type !== 'income' && !$method) {
            $items = InventoryItem::query();

            if ($search) {
                $items->where('name', 'like', "%{$search}%");
            }
            if ($dateFrom) {
                $items->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $items->whereDate('created_at', '<=', $dateTo);
            }

            $expense = $items->get()->map(fn ($i) => [
                'type'   => 'expense',
                'amount' => (float) $i->price,
                'day'    => Carbon::parse($i->created_at)->format('Y-m-d'),
            ]);
        }

        // ── Group by day ────────────────────────────────────────────────
        $rows = $income->concat($expense);

        $daily = $rows
            ->groupBy('day')
            ->map(function ($dayRows, $day) {
                $in = $dayRows->where('type', 'income');
                $ex = $dayRows->where('type', 'expense');

                return [
                    'date'    => $day,
                    'orders'  => $in->count(),
                    'items'   => $ex->count(),
                    'income'  => $in->sum('amount'),
                    'expense' => $ex->sum('amount'),
                    'net'     => $in->sum('amount') - $ex->sum('amount'),
                ];
            })
            ->sortByDesc('date')
            ->values();

        // ── Stats (all days in the current filter) ──────────────────────
        $totalTransactions = $rows->count();
        $totalIncome       = $income->sum('amount');
        $totalExpense      = $expense->sum('amount');
        $netBalance        = $totalIncome - $totalExpense;

        // ── Manual pagination (by day) ──────────────────────────────────
        $perPage = 15;
        $page    = LengthAwarePaginator::resolveCurrentPage();

        $transactions = new LengthAwarePaginator(
            $daily->forPage($page, $perPage)->values(),
            $daily->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $methods = Order::where('status', 'completed')
            ->whereNotNull('payment_method')
            ->distinct()
            ->pluck('payment_method');

        return view('pages/transactions.index', compact(
            'transactions', 'methods', 'dateFrom', 'dateTo',
            'totalTransactions', 'totalIncome', 'totalExpense', 'netBalance'
        ));
    }
}
