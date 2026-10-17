<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $ref }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }

        @font-face {
            font-family: 'Khmer OS Battambang';
            src: url('{{ storage_path("fonts/KhmerOSbattambang.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            margin: 0;
            padding: 5mm 4mm;
            width: 72mm;
            font-family: 'Khmer OS Battambang', sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #111;
        }

        .copy-break { page-break-after: always; }
        .shop-name { text-align: center; font-size: 20px; font-weight: bold; letter-spacing: 3px; margin: 0; }
        .shop-sub { text-align: center; font-size: 9px; color: #555; margin: 2px 0 0; }
        .receipt-tag { text-align: center; margin: 8px 0 0; }
        .receipt-tag span { display: inline-block; padding: 2px 12px; border: 1px solid #111; border-radius: 10px; font-size: 8.5px; font-weight: bold; letter-spacing: 2px; }
        .copy-label { text-align: center; font-size: 8.5px; color: #555; letter-spacing: 1px; margin: 4px 0 0; }

        .dash   { border-top: 1px dashed #777; margin: 8px 0; height: 0; }
        .solid  { border-top: 1.5px solid #111; margin: 8px 0; height: 0; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td, th { padding: 0; vertical-align: top; word-wrap: break-word; }

        .info td { padding: 1.5px 0; font-size: 9.5px; }
        .info .k { width: 22%; color: #666; }
        .info .v { width: 78%; text-align: right; font-weight: bold; }

        .items th { font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #555; padding: 3px 0; border-bottom: 1px solid #111; }
        .items td { padding: 4px 0; font-size: 10px; border-bottom: 1px dotted #ccc; }
        .items tr:last-child td { border-bottom: none; }

        .c-name  { width: 40%; text-align: left; }
        .c-qty   { width: 12%; text-align: center; }
        .c-price { width: 22%; text-align: right; }
        .c-total { width: 26%; text-align: right; font-weight: bold; }
        .item-note { font-size: 8px; color: #777; }

        .sum td { padding: 2px 0; font-size: 10px; }
        .sum .l { text-align: left;  color: #555; }
        .sum .r { text-align: right; font-weight: bold; }

        .grand td { padding: 4px 0; }
        .grand .l { font-size: 12px; font-weight: bold; letter-spacing: 1px; }
        .grand .r { font-size: 12px; font-weight: bold; text-align: right; }

        .pay-badge { display: inline-block; padding: 1px 8px; border-radius: 8px; background: #111; color: #fff; font-size: 8.5px; font-weight: bold; letter-spacing: 1px; }

        .thanks { text-align: center; font-size: 11px; font-weight: bold; margin: 10px 0 2px; }
        .footer-sub { text-align: center; font-size: 8.5px; color: #666; margin: 0; }
    </style>
</head>
<body>

@php
    $copies = $copies ?? ['CUSTOMER COPY', 'SHOP COPY', 'BARISTA COPY'];
@endphp

@foreach($copies as $copyLabel)
    @php $isCustomer = ($copyLabel === 'CUSTOMER COPY'); @endphp

    <div class="{{ $loop->last ? '' : 'copy-break' }}">

        {{-- Header: customer copy only (shop & barista copies start from Ref) --}}
        @if($isCustomer)
            <p class="shop-name">KR SHOP</p>
            <p class="shop-sub">Phnom Penh, Cambodia</p>
            <div class="receipt-tag"><span>RECEIPT</span></div>

            <div class="dash"></div>
        @endif

        <table class="info">
            <tr><td class="k">Ref</td>     <td class="v">{{ $ref }}</td></tr>
            <tr><td class="k">Date</td>    <td class="v">{{ $date }}</td></tr>
            <tr><td class="k">Cashier</td> <td class="v">{{ $cashier }}</td></tr>
            <tr>
                <td class="k">Payment</td>
                <td class="v"><span class="pay-badge">{{ strtoupper($paymentLabel) }}</span></td>
            </tr>
        </table>

        <div class="dash"></div>

        <table class="items">
            <thead>
            <tr>
                <th class="c-name">Item</th>
                <th class="c-qty">Qty</th>
                <th class="c-price">Price</th>
                <th class="c-total">Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td class="c-name">
                        {{ $item['name'] }}
                        @if(!empty($item['note']))
                            <div class="item-note">{{ $item['note'] }}</div>
                        @endif
                    </td>
                    <td class="c-qty">{{ $item['qty'] }}</td>
                    <td class="c-price">${{ number_format($item['price'], 2) }}</td>
                    <td class="c-total">${{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="dash"></div>

        <table class="sum">
            <tr>
                <td class="l">Items</td>
                <td class="r">{{ $items ? collect($items)->sum('qty') : 0 }}</td>
            </tr>
            @if(($discount ?? 0) > 0.001)
                <tr>
                    <td class="l">Subtotal</td>
                    <td class="r">${{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="l">Discount</td>
                    <td class="r">-${{ number_format($discount, 2) }}</td>
                </tr>
            @endif
        </table>

        <div class="solid"></div>

        <table class="grand">
            <tr>
                <td class="l">Grand Total(USD)</td>
                <td class="r">${{ number_format($total, 2) }}</td>
            </tr>
            <tr>
                <td class="l">Grand Total(KHR)</td>
                <td class="r">{{ number_format($totalkhr, 0) }}Riel</td>
            </tr>
        </table>

        <div class="solid"></div>

        {{-- Customer copy only: cash details + thank-you footer --}}
        @if($isCustomer)
            @if(($paymentMethod ?? '') === 'cash' && ($cashGiven ?? 0) > 0)
                <table class="sum">
                    <tr>
                        <td class="l">Cash received</td>
                        <td class="r">${{ number_format($cashGiven, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="l">Change</td>
                        <td class="r">${{ number_format($cashGiven - $total, 2) }}</td>
                    </tr>
                </table>
                <div class="dash"></div>
            @endif

            <p class="thanks">Thank you for your purchase!</p>
            <p class="footer-sub">Please come again</p>
        @endif

    </div>
@endforeach

</body>
</html>
