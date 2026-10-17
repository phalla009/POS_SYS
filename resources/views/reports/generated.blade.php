@extends('layouts.master')

@section('pageTitle')
    Report
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <style>
        #loading-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.85);
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 99999;
        }
        .spinner {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 60px; height: 60px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        #loading-text { margin-top: 15px; font-size: 16px; color: #333; }

        .btn-export {
            background: repeating-linear-gradient(-45deg, #1d6f42,#105730,#1d6f42 3px,#105730 3px);
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 16px;
            margin-left: 8px;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-export:hover {
            transform: translateY(-1px);
        }
        .btn-export i {
            margin-right: 6px;
        }

        /* Payment method badges */
        .pay-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .pay-badge.cash  { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .pay-badge.bank  { background: #fce7f3; color: #be185d; border: 1px solid #fbcfe8; }
        .pay-badge.other { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }

        /* Financial summary cards */
        .fin-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin: 14px 0 6px;
        }
        .fin-card {
            border-radius: 12px;
            padding: 16px 18px;
            border: 1px solid transparent;
        }
        .fin-card-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }
        .fin-card-value {
            font-size: 24px;
            font-weight: 700;
        }
        .fin-card.sales     { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
        .fin-card.inventory { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
        .fin-card.net       { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .fin-card.net.negative { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .net-negative { color: #dc2626; font-weight: 600; }
        .net-positive { color: #15803d; font-weight: 600; }

        @media (max-width: 768px) {
            .fin-cards { grid-template-columns: 1fr; }
            .fin-card-value { font-size: 20px; }
        }

        /* Responsive Table Wrappers & Full Width Layout Overrides */
        .report-table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-top: 15px;
            border-radius: 8px;
        }

        .report-table-wrapper table.table {
            width: 100% !important;
            display: table !important;
            border-collapse: collapse;
        }

        /* Prevent mobile CSS rules from turning table rows/cells into cards */
        @media (max-width: 768px) {
            .report-table-wrapper table.table,
            .report-table-wrapper table.table thead,
            .report-table-wrapper table.table tbody,
            .report-table-wrapper table.table tfoot,
            .report-table-wrapper table.table tr,
            .report-table-wrapper table.table th,
            .report-table-wrapper table.table td {
                display: table-cell !important;
                width: auto !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .report-table-wrapper table.table thead {
                display: table-header-group !important;
            }

            .report-table-wrapper table.table tbody {
                display: table-row-group !important;
            }

            .report-table-wrapper table.table tfoot {
                display: table-footer-group !important;
            }

            .report-table-wrapper table.table tr {
                display: table-row !important;
            }

            .report-table-wrapper table.table th,
            .report-table-wrapper table.table td {
                padding: 10px 12px !important;
                white-space: nowrap;
            }
        }
    </style>
@endsection

@section('content')

    {{-- Loading Overlay --}}
    <div id="loading-overlay">
        <div class="spinner"></div>
        <div id="loading-text">Going back...</div>
    </div>

    @php
        // Small helper: turns "Cash" / "Bank" / "-" into a coloured badge class
        $payClass = fn ($label) => match ($label) {
            'Cash'  => 'cash',
            'Bank'  => 'bank',
            default => 'other',
        };
    @endphp

    <div class="content-section">

        {{-- Back Button --}}
        <a href="{{ route('reports.index') }}" id="backBtn" class="btn btn-back" style="margin-bottom: 16px; display: inline-block;">
            <i class="fas fa-chevron-left"></i> Back
        </a>

        {{-- Export to Excel Button --}}
        <a href="{{ route('reports.export', ['type' => $type, 'range' => $range]) }}"
           class="btn-export">
            <i class="fas fa-file-excel"></i> Export to Excel
        </a>

        <h2>{{ ucfirst($type) }} Report</h2>
        {{--        <p><b>From</b> {{ $start->format('d M , Y') }} <b>To</b> {{ $end->format('d M, Y') }}</p><br>--}}

        {{-- Sales Report --}}
        @if($type === 'sales')
            <h4>Sale Report ({{ $start->format('d-M-Y') }} - {{ $end->format('d-M-Y') }})</h4>
            <div class="report-table-wrapper">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Total Qty</th>
                        <th>Total ($)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($results as $row)
                        <tr>
                            <td>{{ $row->date }}</td>
                            <td><span class="pay-badge {{ $payClass($row->payment_method) }}">{{ $row->payment_method }}</span></td>
                            <td>{{ number_format($row->qty ?? 0) }}</td>
                            <td>${{ number_format($row->total ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    {{-- Subtotal per payment method --}}
                    <tr style="background-color: #f0fdf4; color: #15803d; font-weight: 700;">
                        <td colspan="2" style="padding: 10px 16px; text-align:right;">Cash Total</td>
                        <td style="padding: 10px 16px;">{{ number_format($results->where('payment_method', 'Cash')->sum('qty')) }}</td>
                        <td style="padding: 10px 16px;">${{ number_format($results->where('payment_method', 'Cash')->sum('total'), 2) }}</td>
                    </tr>
                    <tr style="background-color: #fdf2f8; color: #be185d; font-weight: 700;">
                        <td colspan="2" style="padding: 10px 16px; text-align:right;">Bank Total</td>
                        <td style="padding: 10px 16px;">{{ number_format($results->where('payment_method', 'Bank')->sum('qty')) }}</td>
                        <td style="padding: 10px 16px;">${{ number_format($results->where('payment_method', 'Bank')->sum('total'), 2) }}</td>
                    </tr>
                    <tr style="background-color: #1a73e8; color: #ffffff;">
                        <th colspan="2" style="padding: 12px 16px; font-size: 15px;">Grand Total</th>
                        <th style="padding: 12px 16px; font-size: 15px;">
                            {{ number_format($results->sum('qty')) }}
                        </th>
                        <th style="padding: 12px 16px; font-size: 15px;">
                            ${{ number_format($results->sum('total'), 2) }} USD
                            <br>
                            <small style="color: #e2e8f0;">៛{{ number_format($results->sum('total') * 4000, 0) }} KHR</small>
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Financial Report: Total Sales vs Total Inventory --}}
        @elseif($type === 'financial')
            @php
                $finSales     = $summary['sales']     ?? $results->sum('total_sales');
                $finInventory = $summary['inventory'] ?? $results->sum('total_inventory');
                $finNet       = $summary['net']       ?? ($finSales - $finInventory);
            @endphp

            <h4>Financial Report ({{ $start->format('d-M-Y') }} - {{ $end->format('d-M-Y') }})</h4>

            {{-- Summary cards --}}
            <div class="fin-cards">
                <div class="fin-card sales">
                    <div class="fin-card-label">Total Sales</div>
                    <div class="fin-card-value">${{ number_format($finSales, 2) }}</div>
                </div>
                <div class="fin-card inventory">
                    <div class="fin-card-label">Total Inventory</div>
                    <div class="fin-card-value">${{ number_format($finInventory, 2) }}</div>
                </div>
                <div class="fin-card net {{ $finNet < 0 ? 'negative' : '' }}">
                    <div class="fin-card-label">Net</div>
                    <div class="fin-card-value">{{ $finNet < 0 ? '-' : '' }}${{ number_format(abs($finNet), 2) }}</div>
                </div>
            </div>

            <div class="report-table-wrapper">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sold Qty</th>
                        <th>Total Sales ($)</th>
                        <th>Inventory Items</th>
                        <th>Total Inventory ($)</th>
                        <th>Net ($)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($results as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                            <td>{{ number_format($row->sold_qty ?? 0) }}</td>
                            <td>${{ number_format($row->total_sales ?? 0, 2) }}</td>
                            <td>{{ number_format($row->inventory_items ?? 0) }}</td>
                            <td>${{ number_format($row->total_inventory ?? 0, 2) }}</td>
                            <td class="{{ ($row->net ?? 0) < 0 ? 'net-negative' : 'net-positive' }}">
                                {{ ($row->net ?? 0) < 0 ? '-' : '' }}${{ number_format(abs($row->net ?? 0), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 24px; color:#9ca3af;">No data for this period.</td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr style="background-color: #1a73e8; color: #ffffff;">
                        <th style="padding: 12px 16px; font-size: 15px;">Grand Total</th>
                        <th style="padding: 12px 16px; font-size: 15px;">{{ number_format($results->sum('sold_qty')) }}</th>
                        <th style="padding: 12px 16px; font-size: 15px;">${{ number_format($finSales, 2) }}</th>
                        <th style="padding: 12px 16px; font-size: 15px;">{{ number_format($results->sum('inventory_items')) }}</th>
                        <th style="padding: 12px 16px; font-size: 15px;">${{ number_format($finInventory, 2) }}</th>
                        <th style="padding: 12px 16px; font-size: 15px;">
                            {{ $finNet < 0 ? '-' : '' }}${{ number_format(abs($finNet), 2) }}
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Inventory Report --}}
        @elseif($type === 'inventory')
            @php
                $grandTotalUSD = 0;
                $totalQty      = 0;
                $rate          = 4100;
            @endphp
            <div class="report-table-wrapper">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price ($)</th>
                        <th>Total ($)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($results as $row)
                        @php
                            $qty   = $row->quantity ?? $row->stock ?? 0;
                            $total = $qty * $row->price;
                            $grandTotalUSD += $total;
                            $totalQty      += $qty;
                        @endphp
                        <tr>
                            <td>{{ $row->name }}</td>
                            <td>{{ $qty }}</td>
                            <td>${{ number_format($row->price, 2) }}</td>
                            <td>${{ number_format($total, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr style="background-color: #1a73e8; color: #ffffff;">
                        <th style="padding: 10px 14px;">Total Qty:</th>
                        <th style="padding: 10px 14px;">{{ $totalQty }}</th>
                        <th style="text-align:right; padding: 10px 14px;">Grand Total (USD):</th>
                        <th style="padding: 10px 14px;">${{ number_format($grandTotalUSD, 2) }}</th>
                    </tr>
                    <tr style="background-color: #1557b0; color: #ffffff;">
                        <th colspan="3" style="text-align:right; padding: 10px 14px;">Grand Total (KHR):</th>
                        <th style="padding: 10px 14px;">៛{{ number_format($grandTotalUSD * $rate) }}</th>
                    </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Customer Report --}}
        @elseif($type === 'customer')
            <div class="report-table-wrapper">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Gender</th>
                        <th class="text-center">Total Qty</th>
                        <th class="text-right">Total Price</th>
                        <th>Joined</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($results as $row)
                        <tr>
                            <td>{{ $row->name }}</td>
                            <td>{{ ucfirst($row->gender) }}</td>
                            <td class="text-center">{{ number_format($row->total_qty ?? 0) }}</td>
                            <td class="text-right">${{ number_format($row->total_price ?? 0, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->created_at)->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr style="background-color: #e3f2fd; color: #0d47a1; font-weight: bold;">
                        <td colspan="2" class="text-right">Grand Total:</td>
                        <td class="text-center">{{ number_format($results->sum('total_qty')) }}</td>
                        <td class="text-right">${{ number_format($results->sum('total_price'), 2) }}</td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        @endif

    </div>

    <div id="logout-confirm">
        <div class="confirm-box">
            <div class="icon-container">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <p>Are you sure you want to logout?</p>
            <button id="confirm-yes">Yes, Logout!</button>
            <button id="confirm-no">No, Keep it!</button>
        </div>
    </div>

    <script>
        const logoutLink    = document.getElementById('logout-link');
        const logoutConfirm = document.getElementById('logout-confirm');
        const confirmYes    = document.getElementById('confirm-yes');
        const confirmNo     = document.getElementById('confirm-no');
        const logoutForm    = document.getElementById('logout-form');

        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                e.preventDefault();
                logoutConfirm.style.display = 'flex';
            });
        }
        if (confirmYes) confirmYes.addEventListener('click', function() { logoutForm.submit(); });
        if (confirmNo)  confirmNo.addEventListener('click',  function() { logoutConfirm.style.display = 'none'; });

        document.getElementById('backBtn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('loading-overlay').style.display = 'flex';
            window.location.href = this.getAttribute('href');
        });
    </script>

@endsection
