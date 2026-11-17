<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\TelegramNotifier;

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
    public function sendTelegram(Request $request)
    {
        $from = $request->filled('date_from') ? $request->date_from : null;
        $to   = $request->filled('date_to')   ? $request->date_to   : null;

        $toKhr = fn ($usd) => round(($usd * 4000) / 100) * 100;

        // ── Income ──
        $orders = Order::with('product')
            ->where('status', 'completed')
            ->when($from, fn ($q) => $q->whereDate('order_date', '>=', $from))
            ->when($to,   fn ($q) => $q->whereDate('order_date', '<=', $to))
            ->get();

        $revenue  = (float) $orders->sum('total_amount');
        $qty      = (int) $orders->sum('quantity');
        $invoices = $orders->map(fn ($o) => $o->pos_ref ?? $o->order_number)->unique()->count();

        $subtotal = $orders->sum(fn ($o) => (float) optional($o->product)->price * $o->quantity);
        $discount = max(0, round($subtotal - $revenue, 2));

        $cashUsd = (float) $orders->where('payment_method', 'cash')->sum('total_amount');
        $bankUsd = (float) $orders->where('payment_method', 'khqr')->sum('total_amount');

        // ── Expense ──
        $expense = (float) InventoryItem::query()
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('price');

        $net = $revenue - $expense;

        // ── Top items ──
        $top = $orders
            ->groupBy(fn ($o) => optional($o->product)->name ?? 'Item')
            ->map(fn ($g) => $g->sum('quantity'))
            ->sortDesc()
            ->take(5);

        $topText = $top->isEmpty()
            ? "-\n"
            : $top->map(fn ($q, $name) => '• ' . e($name) . " x{$q}")->implode("\n") . "\n";

        // ── Message ──
        $line  = '━━━━━━━━━━━━━━━━━━';
        $line2 = '──────────────────';
        $fmtD  = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d-m-Y') : 'All';

        $msg  = "📊 <b>DAILY SUMMARY REPORT</b>\n";
        $msg .= "{$line}\n";
        $msg .= "📅 From: {$fmtD($from)} To: {$fmtD($to)}\n";
        $msg .= "{$line}\n";
        $msg .= "🧾 Total Invoice: {$invoices}\n";
        $msg .= "📦 Total Qty: {$qty}\n";
        $msg .= "🏷️ Total Discount: $" . number_format($discount, 2) . "\n";
        $msg .= "💰 Total Revenue (USD): $" . number_format($revenue, 2) . "\n";
        $msg .= "🇰🇭 Total Revenue (KHR): ៛" . number_format($toKhr($revenue)) . "\n";
        $msg .= "{$line2}\n";
        $msg .= "💵 Pay by Cash: ៛" . number_format($toKhr($cashUsd)) . "\n";
        $msg .= "🏦 Pay by Bank / KHQR: $" . number_format($bankUsd, 2) . "\n";
        $msg .= "{$line2}\n";
        $msg .= "📉 Total Expense (USD): $" . number_format($expense, 2) . "\n";
        $msg .= "🇰🇭 Total Expense (KHR): ៛" . number_format($toKhr($expense)) . "\n";
        $msg .= "📈 Net Profit (USD): " . ($net < 0 ? '-' : '') . "$" . number_format(abs($net), 2) . "\n";
        $msg .= "🇰🇭 Net Profit (KHR): " . ($net < 0 ? '-' : '') . "៛" . number_format(abs($toKhr($net))) . "\n";
        $msg .= "{$line}\n";
        $msg .= "🏆 <b>TOP ITEMS:</b>\n";
        $msg .= $topText;
        $msg .= "{$line}\n";
        $msg .= "🕒 " . now()->format('d-m-Y h:i A');

        $ok = TelegramNotifier::send($msg);

        return back()->with(
            $ok ? 'success' : 'error',
            $ok ? 'Summary report sent to Telegram.' : 'Failed to send to Telegram. Check storage/logs/laravel.log.'
        );
    }
}
