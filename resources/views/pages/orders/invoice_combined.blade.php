<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Invoice</title>
<style>
    /* 
        Table-based, no flexbox/grid/box-shadow — this view is rendered
        both by the browser (window.print()) AND by dompdf (PDF download),
        and dompdf only supports a limited CSS subset.

        Widths below are FIXED POINTS, optimized for 72mm thermal receipt.
    */
    @page {
        size: 72mm auto;
        margin: 0;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Courier New', Courier, monospace;
        font-size: 9px;
        color: #000;
        width: 100%;
        padding: 4px;
    }

    .breakable {
        word-break: break-all;
        overflow-wrap: anywhere;
    }

    .center { text-align: center; }
    .right  { text-align: right; }
    .bold   { font-weight: bold; }

    .shop-name {
        font-size: 14px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    .shop-meta {
        font-size: 8px;
        margin-top: 2px;
    }

    hr.dashed {
        border: none;
        border-top: 1px dashed #000;
        margin: 5px 0;
    }

    .meta-table {
        width: 100%;
        margin-bottom: 4px;
    }
    .meta-table td { padding: 1px 0; vertical-align: top; }

    table.items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }

    table.items th {
        border-bottom: 1px dashed #000;
        padding: 2px 1px;
        font-size: 8px;
        text-align: left;
    }

    table.items td {
        padding: 2px 1px;
        font-size: 9px;
        vertical-align: top;
    }

    .totals-table {
        width: 100%;
        margin-top: 5px;
        font-size: 9.5px;
    }
    .totals-table td { padding: 1px 0; }

    .grand-total td {
        font-size: 11px;
        font-weight: bold;
        border-top: 1px dashed #000;
        padding-top: 3px;
    }

    .footer {
        margin-top: 8px;
        font-size: 8px;
        text-align: center;
    }
</style>
</head>
<body>

    <div class="center">
        <div class="shop-name">KR SHOP</div>
        <div class="shop-meta">Phnom Penh, Cambodia</div>
    </div>

    <hr class="dashed">

    @php
        $firstOrder = $orders->first();
        $posRef     = $firstOrder->pos_ref ?? null;
        $grandTotal = $orders->sum('total_amount');
    @endphp

    <table class="meta-table">
        <colgroup>
            <col style="width: 45pt;">
            <col style="width: 127pt;">
        </colgroup>
        <tr>
            <td>Ref:</td>
            <td class="right bold breakable">{{ $posRef ?? $firstOrder->order_number }}</td>
        </tr>
        <tr>
            <td>Date:</td>
            <td class="right">{{ \Carbon\Carbon::parse($firstOrder->order_date)->format('d/m/y') }}</td>
        </tr>
        @if($firstOrder->customer)
        <tr>
            <td>Cust:</td>
            <td class="right breakable">{{ $firstOrder->customer->name }}</td>
        </tr>
        @endif
        <tr>
            <td>Cashier:</td>
            <td class="right breakable">{{ Auth::user()->name ?? '—' }}</td>
        </tr>
    </table>

    <hr class="dashed">

    <table class="items">
        <colgroup>
            <col style="width: 65pt;">
            <col style="width: 22pt;">
            <col style="width: 42pt;">
            <col style="width: 43pt;">
        </colgroup>
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td class="breakable">{{ $order->product->name ?? 'Unknown item' }}</td>
                <td class="right">{{ $order->quantity }}</td>
                <td class="right">${{ number_format($order->quantity > 0 ? $order->total_amount / $order->quantity : 0, 2) }}</td>
                <td class="right">${{ number_format($order->total_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <colgroup>
            <col style="width: 130pt;">
            <col style="width: 42pt;">
        </colgroup>
        <tr>
            <td>Items</td>
            <td class="right">{{ $orders->sum('quantity') }}</td>
        </tr>
        <tr class="grand-total">
            <td>TOTAL</td>
            <td class="right">${{ number_format($grandTotal, 2) }}</td>
        </tr>
    </table>

    <hr class="dashed">

    <div class="footer">
        Thank you for your purchase!
    </div>

</body>
</html>