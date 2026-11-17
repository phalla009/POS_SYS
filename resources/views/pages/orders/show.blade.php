@extends('layouts.master')

@section('pageTitle')
    Orders Details
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <script src="{{ URL::asset('js/form.js') }}"></script>
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

        /* ឱ្យ modal ពង្រីកតាមចំនួន items */
        .modal-content { height: auto; min-height: 480px; }

        .info-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }

        .info-row-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .info-row,
            .info-row-2 {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                margin-bottom: 12px;
            }
        }

        @media (max-width: 480px) {
            .info-row,
            .info-row-2 {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            .info-box { padding: 10px 12px; }
            .info-value { font-size: 13px; }
        }

        .info-box {
            padding: 14px 18px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .info-box:hover {
            border-color: #a5b4fc;
            box-shadow: 0 4px 12px rgba(99,102,241,0.08);
        }

        .info-label {
            font-size: 11px;
            font-weight: 700;
            color: #4338ca;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-label i { font-size: 11px; color: #6366f1; }

        .info-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
            white-space: pre-wrap;
            display: flex;
            align-items: center;
            word-break: break-word;
        }

        .status-badge {
            display: inline-block;
            padding: 0px;
            border-radius: 24px;
            font-size: 12px;
        }
        .status-pending   { background: none; color: #a16207; border: none; }
        .status-completed { background: none; color: #16a34a; border: none; }
        .status-cancelled { background: none; color: #dc2626; border: none; }

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

        /* Sugar level label next to product name */
        .sugar-tag {
            margin-left: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
        }

        /* Items table */
        .items-wrap { overflow-x: auto; margin-bottom: 14px; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th, .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 14px;
            white-space: nowrap;
        }
        .items-table th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #fffaf6;
        }
        .items-table tfoot th {
            border-top: 2px solid #e5e7eb;
            border-bottom: none;
            font-size: 14px;
            color: #1f2937;
            text-transform: none;
            letter-spacing: 0;
        }
    </style>
@endsection

@section('content')

    {{-- Loading Overlay --}}
    <div id="loading-overlay">
        <div class="spinner"></div>
        <div id="loading-text">Going back...</div>
    </div>

    @if(session('success'))
        <div id="successMessage" class="custom-success">
            <i class="fas fa-check-circle" style="color: green; margin-right: 8px;"></i>
            {{ session('success') }}
        </div>
    @endif

    @php
        // POS saves 'cash' / 'khqr'. Show them as Cash / Bank.
        $payLabel = match (strtolower($order->payment_method ?? '')) {
            'cash'  => 'Cash',
            'khqr'  => 'Bank',
            default => '-',
        };
        $payClass = match ($payLabel) {
            'Cash'  => 'cash',
            'Bank'  => 'bank',
            default => 'other',
        };

        // Totals សម្រាប់ invoice ទាំងមូល
        $totalQty     = $items->sum('quantity');
        $totalSelling = $items->sum('total_amount');
        $totalCost    = $items->sum(fn ($i) => ($i->product->price ?? 0) * $i->quantity);
        $totalDiscount = max(0, round($totalCost - $totalSelling, 2));
        $invoiceNo    = $order->pos_ref ?? $order->order_number;
    @endphp

    <div class="modal-content" role="main" aria-label="Order Details">
        <a href="{{ route('orders.index') }}" id="backBtn" class="btn btn-back">
            <i class="fas fa-chevron-left"></i> Back
        </a>

        <h2><i class="fas fa-shopping-cart"></i> Order Details</h2><br>

        {{-- Row 1: Invoice, Customer, Status --}}
        <div class="info-row">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-hashtag"></i> Invoice</div>
                <div class="info-value">{{ $invoiceNo }}</div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-user"></i> Customer</div>
                <div class="info-value">{{ optional($order->customer)->name ?? 'N/A' }}</div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-circle"></i> Status</div>
                <div class="info-value">
                    <span class="status-badge status-{{ strtolower($order->status) }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Items ទាំងអស់ក្នុង invoice --}}
        <div class="info-label" style="margin-bottom:8px;">
            <i class="fas fa-box"></i> Items ({{ $items->count() }})
        </div>
        <div class="items-wrap">
            <table class="items-table">
                <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Amount</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->order_number }}</td>
                        <td>
                            {{ optional($item->product)->name ?? 'N/A' }}
                            {{-- Sugar level is saved inside the order note: "... · sugar: 50%" --}}
                            @if(preg_match('/sugar:\s*(\d+%)/u', (string) $item->note, $sugarMatch))
                                <span class="sugar-tag">(sugar {{ $sugarMatch[1] }})</span>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->product->price ?? 0, 2) }}</td>
                        <td>
                            {{-- Discount is saved inside the order note: "... · discount: 10%" or "$5" --}}
                            @if(preg_match('/discount:\s*([^\s·]+)/u', (string) $item->note, $discMatch))
                                {{ $discMatch[1] }}
                            @else
                                -
                            @endif
                        </td>
                        <td>${{ number_format($item->total_amount ?? 0, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2">Total</th>
                    <th>{{ $totalQty }}</th>
                    <th></th>
                    <th>{{ $totalDiscount > 0 ? '-$' . number_format($totalDiscount, 2) : '' }}</th>
                    <th>${{ number_format($totalSelling, 2) }}</th>
                </tr>
                </tfoot>
            </table>
        </div>

        {{-- Row 2: Order Date, Payment Method --}}
        <div class="info-row-2">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-calendar-alt"></i> Order Date</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($order->order_date)->format('d-M-Y \a\t g:i A') }}</div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-credit-card"></i> Payment Method</div>
                <div class="info-value">
                    <span class="pay-badge {{ $payClass }}">{{ $payLabel }}</span>
                </div>
            </div>
        </div>

        {{-- Row 3: Cost Amount, Selling Amount --}}
        <div class="info-row-2">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-dollar-sign"></i> Cost Amount</div>
                <div class="info-value">${{ number_format($totalCost, 2) }}</div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-cash-register"></i> Selling Amount</div>
                <div class="info-value">${{ number_format($totalSelling, 2) }}</div>
            </div>
        </div>

        {{-- Row 4: Note --}}
        <div class="info-row-2">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-sticky-note"></i> Note</div>
                <div class="info-value">{{ $order->note ?? 'N/A' }}</div>
            </div>
        </div>

        {{-- Row 5: Created, Updated --}}
        <div class="info-row-2">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-calendar-plus"></i> Created</div>
                <div class="info-value">{{ $order->created_at->format('d-M-Y \a\t g:i A') }}</div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-clock"></i> Last Updated</div>
                <div class="info-value">{{ $order->updated_at->format('d-M-Y \a\t g:i A') }}</div>
            </div>
        </div>

    </div>

    <script>
        document.getElementById('backBtn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('loading-overlay').style.display = 'flex';
            window.location.href = this.getAttribute('href');
        });
    </script>

@endsection
