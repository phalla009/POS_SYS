<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'product'])
            ->withSum('payments', 'amount');

        // customer_id/status/search remain supported as query params (e.g. the
        // "View Pending" banner link below still filters by status) even
        // though their standalone filter inputs were removed from the UI.
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Date range filter: defaults to "today → today" whenever the URL
        // doesn't explicitly carry date_from/date_to (fresh page load, sidebar
        // links, etc). If the user submits the filter form with both date
        // fields cleared, we show orders across all dates. Either end can be
        // left open to make it a "from X onward" or "up to X" filter.
        if ($request->has('date_from') || $request->has('date_to')) {
            $dateFrom = $request->filled('date_from') ? $request->date_from : null;
            $dateTo   = $request->filled('date_to') ? $request->date_to : null;

            if ($dateFrom) {
                $query->whereDate('order_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('order_date', '<=', $dateTo);
            }
        } else {
            $today    = now()->format('Y-m-d');
            $dateFrom = $today;
            $dateTo   = $today;
            $query->whereDate('order_date', $today);
        }

        // Count pending orders, and sum quantity/price, across ALL matching
        // results (not just current page) before pagination limits the query.
        $pendingCount = (clone $query)->where('status', 'pending')->count();

        // "Total Invoice" = number of SALES, not number of order rows. A POS
        // checkout with 3 cart items creates 3 `orders` rows sharing one
        // `pos_ref`, and those should count as 1 invoice, not 3. Orders
        // created via the normal "Add New Order" flow have pos_ref = null,
        // so each one is its own invoice (grouped by its own id).
        // Only id/pos_ref are pulled to keep this cheap even with eager
        // loads (with/withSum) already attached to $query.
        $totalInvoice = (clone $query)
            ->get(['id', 'pos_ref'])
            ->unique(fn ($order) => $order->pos_ref ?? $order->id)
            ->count();

        $totalQty = (clone $query)->sum('quantity');

        // These three are independent of the date-range filter above — they
        // always reflect today, the current calendar month, and all time.
        $dailyTotal   = Order::whereDate('order_date', now()->format('Y-m-d'))->sum('total_amount');
        $monthlyTotal = Order::whereYear('order_date', now()->year)
                              ->whereMonth('order_date', now()->month)
                              ->sum('total_amount');
        $grandTotal   = Order::sum('total_amount');

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('pages/orders.index', compact(
            'orders', 'pendingCount', 'dateFrom', 'dateTo',
            'totalInvoice', 'totalQty', 'dailyTotal', 'monthlyTotal', 'grandTotal'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::all();
        $products  = Product::where('status', 'active')->get();
        return view('pages/orders.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id'  => 'required|exists:products,id',
            'quantity'    => 'required|integer|min:1',
            'status'      => 'required|in:pending,completed,cancelled',
            'order_date'  => 'required|date',
            'note'        => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->status !== 'active') {
            return redirect()->back()->withErrors([
                'product_id' => 'The selected product is inactive and cannot be ordered.'
            ])->withInput();
        }

        if ($product->stock < $request->quantity) {
            return redirect()->back()->withErrors([
                'quantity' => 'The order quantity exceeds available stock (' . $product->stock . ').'
            ])->withInput();
        }

        $totalAmount = $product->price * $request->quantity;

        $lastOrder   = Order::where('order_number', 'like', 'ORD-KR%')
                            ->orderBy('id', 'desc')
                            ->first();
        $newNumber   = $lastOrder ? intval(substr($lastOrder->order_number, 6)) + 1 : 1;
        $orderNumber = 'ORD-KR' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        Order::create([
            'order_number' => $orderNumber,
            'customer_id'  => $request->customer_id,
            'product_id'   => $request->product_id,
            'quantity'     => $request->quantity,
            'total_amount' => $totalAmount,
            'status'       => $request->status,
            'order_date'   => $request->order_date,
            'note'         => $request->note,
        ]);

        $product->stock -= $request->quantity;
        $product->save();

        return redirect()->route('orders.create')->with('success', 'Order added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with(['customer', 'product'])
                    ->withSum('payments', 'amount')
                    ->findOrFail($id);

        return view('pages/orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $order     = Order::findOrFail($id);
        $customers = Customer::all();
        $products  = Product::all();
        return view('pages/orders.edit', compact('order', 'customers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order          = Order::findOrFail($id);
        $oldQuantity    = $order->quantity;
        $product        = Product::findOrFail($order->product_id);
        $newQuantity    = $request->quantity;
        $availableStock = $product->stock + $oldQuantity;

        if ($newQuantity > $availableStock) {
            return redirect()->back()->withErrors([
                'quantity' => 'The ordered quantity exceeds available stock'
            ])->withInput();
        }

        $product->stock = $availableStock - $newQuantity;
        $product->save();

        $order->quantity     = $newQuantity;
        $order->total_amount = $newQuantity * $product->price;
        $order->customer_id  = $request->customer_id;
        $order->status       = $request->status;
        $order->order_date   = $request->order_date;
        $order->note         = $request->note;
        $order->save();

        return redirect()->route('orders.index')->with('success', 'Order updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }

    /**
     * Bulk delete selected orders.
     */
    public function bulkDestroy(Request $request)
    {
        Order::whereIn('id', $request->ids)->delete();
        return redirect()->route('orders.index')->with('success', 'Selected orders deleted successfully.');
    }

    /**
     * Show payment form inside modal.
     */
    public function payment($id)
    {
        $order = Order::with(['customer', 'product'])->findOrFail($id);
        return view('payments.payment', compact('order'));
    }

    /**
     * Process payment for an order.
     */
    public function pay(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'payment_notes'  => 'nullable|string',
        ]);

        $order = Order::findOrFail($id);

        // ✅ Column is 'amount' — consistent with PaymentController and Blade view
        Payment::create([
            'order_id'       => $order->id,
            'amount'         => $order->total_amount,
            'payment_method' => $request->payment_method,
            'paid_at'        => now(),
        ]);

        $order->payment_method = $request->payment_method;
        $order->payment_notes  = $request->payment_notes;
        $order->status         = 'completed';
        $order->save();

        return redirect()->route('orders.index')->with('success', 'Payment processed successfully.');
    }

    /**
     * Return a partial HTML invoice fragment for single order print.
     * Route: GET /orders/{order}/invoice-partial
     */
    public function invoicePartial(Order $order)
    {
        $order->load(['customer', 'product']);
        return view('pages/orders.invoice_partial', compact('order'));
    }

    /**
     * Return a combined 80mm receipt invoice for multiple selected orders.
     * Route: GET /orders/invoice-combined?ids[]=1&ids[]=2
     */
    public function invoiceCombined(Request $request)
    {
        $orders = $this->getInvoiceOrders($request);

        return view('pages/orders.invoice_combined', compact('orders'));
    }

    /**
     * Same combined invoice as invoiceCombined(), but rendered to a
     * downloadable PDF instead of an HTML page for browser printing.
     * Reuses the same orders.invoice_combined view so the printed and
     * downloaded versions never drift apart.
     * Route: GET /orders/invoice-combined/pdf?ids[]=1&ids[]=2
     */
    public function invoiceCombinedPdf(Request $request)
    {
        $orders = $this->getInvoiceOrders($request);

        $posRef = $orders->first()->pos_ref ?? null;

        // 80mm receipt width (226.77pt) x a tall single page (~200mm/566.93pt).
        // dompdf will still overflow onto additional pages if the content is
        // longer than this, so it's a safe default rather than a hard cap.
        // Adjust to 'A5'/'A4' portrait if your invoice_combined view is
        // actually styled as a full-page document rather than a receipt.
        $pdf = Pdf::loadView('pages/orders.invoice_combined', compact('orders'))
            ->setPaper([0, 0, 226.77, 566.93]);

        $filename = 'invoice-' . ($posRef ?: now()->format('Ymd-His')) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Shared lookup for the combined invoice — used by both invoiceCombined()
     * (HTML/print) and invoiceCombinedPdf() (PDF download) so the two never
     * fall out of sync.
     */
    private function getInvoiceOrders(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:orders,id',
        ]);

        return Order::with(['customer', 'product'])
            ->whereIn('id', $request->input('ids'))
            ->orderBy('id')
            ->get();
    }
}