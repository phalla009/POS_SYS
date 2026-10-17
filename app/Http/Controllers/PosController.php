<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /** Allowed sugar levels (whitelist) */
    private const SUGAR_LEVELS = ['0%', '25%', '50%', '75%', '100%'];

    /**
     * Show the POS page.
     * Only show products that are active AND marked as Add to POS.
     */
    public function index()
    {
        $products = Product::with(['images', 'category'])
            ->where('add_to_pos', 1)
            ->get();

        return view('pos.index', compact('products'));
    }

    /**
     * Process the POS checkout.
     * Saves each cart item as a separate Order row, matching OrderController pattern.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'items'          => 'required|string',
            'total'          => 'required|numeric|min:0',
            'payment_method' => 'nullable|in:cash,khqr',
        ]);

        $items = json_decode($request->input('items'), true);

        if (empty($items) || !is_array($items)) {
            return redirect()->route('pos.index')->withErrors(['items' => 'Cart is empty.']);
        }

        $paymentMethod = $request->input('payment_method');
        $cashGiven     = (float) $request->input('cash_given', 0);

        $posRef = 'POS-' . strtoupper(substr(uniqid(), -6)) . '-' . now()->format('His');

        $savedCount = 0;
        $totalSaved = 0;
        $errors     = [];
        $orderIds   = [];

        DB::transaction(function () use ($items, $posRef, $paymentMethod, &$savedCount, &$totalSaved, &$errors, &$orderIds) {

            foreach ($items as $item) {

                $productId = $item['id']       ?? null;
                $qty       = (int)($item['qty']    ?? 1);
                $discount  = $item['discount']     ?? '0';

                // ✅ Sugar level (optional). Only accept known values.
                $sugar = trim((string) ($item['sugar'] ?? ''));
                if (!in_array($sugar, self::SUGAR_LEVELS, true)) {
                    $sugar = '';
                }

                if (!$productId || $qty < 1) continue;

                $product = Product::find($productId);

                if (!$product) {
                    $errors[] = "Product ID {$productId} not found.";
                    continue;
                }

                if (!$product->add_to_pos) {
                    $errors[] = "\"{$product->name}\" is not available in POS.";
                    continue;
                }

                if ($product->stock < $qty) {
                    $errors[] = "Insufficient stock for \"{$product->name}\" (available: {$product->stock}).";
                    continue;
                }

                // Always use the price from the DATABASE, not from the request
                $price     = (float) $product->price;
                $lineTotal = $price * $qty;

                // Apply discount
                if ($discount !== '0' && $discount !== '') {
                    if (str_contains($discount, '%')) {
                        $pct       = (float) $discount;
                        $lineTotal -= $lineTotal * ($pct / 100);
                    } elseif (str_contains($discount, '$')) {
                        // Flat discount applied once per line, not multiplied by qty
                        $flat      = (float) $discount;
                        $lineTotal -= $flat;
                    }
                    if ($lineTotal < 0) $lineTotal = 0;
                }

                // Generate order number
                $lastOrder   = Order::where('order_number', 'like', 'ORD-KR%')
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();
                $newNumber   = $lastOrder ? intval(substr($lastOrder->order_number, 6)) + 1 : 1;
                $orderNumber = 'ORD-KR' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                // ✅ Build the note from parts: ref · discount · sugar
                $noteParts = ["POS sale · ref: {$posRef}"];
                if ($discount !== '0' && $discount !== '') {
                    $noteParts[] = "discount: {$discount}";
                }
                if ($sugar !== '') {
                    $noteParts[] = "sugar: {$sugar}";
                }

                $order = Order::create([
                    'order_number'   => $orderNumber,
                    'customer_id'    => null,
                    'product_id'     => $product->id,
                    'quantity'       => $qty,
                    'total_amount'   => round($lineTotal, 2),
                    'status'         => 'completed',
                    'order_date'     => now()->toDateString(),
                    'payment_method' => $paymentMethod,
                    'note'           => implode(' · ', $noteParts),
                    'pos_ref'        => $posRef,
                ]);

                $orderIds[] = $order->id;

                // Deduct stock
                $product->stock -= $qty;
                $product->save();

                $savedCount++;
                $totalSaved += $lineTotal;
            }
        });

        if ($savedCount === 0) {
            return redirect()->route('pos.index')
                ->withErrors($errors)
                ->withInput();
        }

        $message = "{$savedCount} item(s) saved · Total: $" . number_format($totalSaved, 2);

        if (!empty($errors)) {
            $message .= ' · ' . count($errors) . ' item(s) skipped due to stock issues.';
        }

        return redirect()->route('pos.index')
            ->with('pos_success', $message)
            ->with('pos_order_ids', $orderIds)
            ->with('pos_cash_given', $cashGiven);
    }

    /**
     * Combined PDF receipt (80mm) for the orders created by ONE checkout.
     * URL:  /pos/receipt?ids=12,13,14&cash=5.00
     *   ids  = order IDs from the checkout (required)
     *   cash = cash received (optional, only used for cash payments)
     */
    public function receipt(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values();

        abort_if($ids->isEmpty(), 404);

        $orders = Order::with('product')
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        abort_if($orders->isEmpty(), 404);

        $first = $orders->first();

        // One receipt line per order row
        $items = $orders->map(function ($o) {
            $unit = (float) optional($o->product)->price;

            // Collect notes saved in the order note: discount + sugar
            $notes = [];

            if (preg_match('/discount:\s*([^\s·]+)/u', (string) $o->note, $m)) {
                $notes[] = 'Discount ' . $m[1];
            }
            if (preg_match('/sugar:\s*(\d+%)/u', (string) $o->note, $m)) {
                $notes[] = 'Sugar ' . $m[1];
            }

            return [
                'name'  => optional($o->product)->name ?? 'Item',
                'qty'   => (int) $o->quantity,
                'price' => $unit,
                'total' => (float) $o->total_amount,
                'note'  => $notes ? implode(' · ', $notes) : null,
            ];
        })->values()->all();

        $total    = (float) $orders->sum('total_amount');
        $totalkhr = round(($total * 4000) / 100) * 100;
        $subtotal = (float) collect($items)->sum(fn ($i) => $i['price'] * $i['qty']);
        $discount = max(0, round($subtotal - $total, 2));

        $paymentMethod = $first->payment_method;
        $cashGiven     = (float) $request->query('cash', session('pos_cash_given', 0));

        $data = [
            'ref'           => $first->pos_ref ?? $first->order_number,
            'date'          => $first->created_at->format('d/m/Y h:i A'),
            'cashier'       => optional(auth()->user())->name ?? 'Administrator',
            'paymentMethod' => $paymentMethod,
            'paymentLabel'  => match ($paymentMethod) {
                'cash'  => 'Cash',
                'khqr'  => 'Bank',
                default => '-',
            },
            'items'         => $items,
            'subtotal'      => $subtotal,
            'discount'      => $discount,
            'total'         => $total,
            'totalkhr'      => $totalkhr,
            'cashGiven'     => $cashGiven,
        ];

        // 80mm = 226.77pt wide. Height grows with the number of lines.
        $height = 310 + count($items) * 32
            + ($discount > 0 ? 28 : 0)
            + ($paymentMethod === 'cash' && $cashGiven > 0 ? 40 : 0);

        $pdf      = Pdf::loadView('pos.receipt', $data) ->setPaper([0, 0, 226.77, $height], 'portrait');

        // កំណត់ Path របស់ Font ឱ្យ DomPDF ស្គាល់
        $pdf->getDomPDF()->set_option('fontDir', storage_path('fonts/'));
        $pdf->getDomPDF()->set_option('fontCache', storage_path('fonts/'));
        $pdf->getDomPDF()->set_option('defaultFont', 'Khmer OS Battambang');
        $filename = 'receipt-' . $data['ref'] . '.pdf';

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
