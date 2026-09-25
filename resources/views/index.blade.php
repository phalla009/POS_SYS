@extends('layouts.master')

@section('pageTitle') Dashboard @endsection

@section('headerBlock')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/dashboard.css') }}">

    <style>
        .charts-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .charts-grid .card {
            flex: 1 1 calc(50% - 20px);
            min-width: 0;
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            cursor: grab !important;
            user-select: none;
            box-sizing: border-box;
        }

        .charts-grid .card.full-width {
            flex: 1 1 100%;
        }

        .charts-grid .card:active {
            cursor: grabbing !important;
        }

        .sortable-ghost {
            opacity: 0.3;
            background: #f0f0f0;
            border: 2px dashed #030304;
        }

        .sortable-drag {
            background: #ffffff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        /* Canvas Wrapper */
        .chart-container {
            position: relative;
            height: 280px;
            width: 100%;
        }

        .chart-container.tall {
            height: 320px;
        }

        /* Desktop Table Styling */
        .table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        .table-container th,
        .table-container td {
            padding: 10px 12px;
            text-align: left;
        }

        /* Hide Mobile Cards on Desktop */
        .order-cards-mobile {
            display: none;
        }

        /* --- Mobile & Tablet Breakpoints --- */
        @media (max-width: 768px) {
            .charts-grid {
                gap: 15px;
            }

            .charts-grid .card {
                flex: 1 1 100%;
                padding: 1rem;
            }

            .welcome-banner {
                padding: 15px !important;
            }

            .welcome-banner h2 {
                font-size: 1.25rem;
                flex-wrap: wrap;
            }

            .chart-container {
                height: 220px;
            }

            .chart-container.tall {
                height: 260px;
            }

            /* Hide Desktop Table View on Mobile */
            .desktop-table-view {
                display: none;
            }

            /* Mobile View Container with Scroll */
            .order-cards-mobile {
                display: flex;
                flex-direction: column;
                gap: 12px;
                max-height: 220px; /* កំណត់កម្ពស់ត្រឹមបង្ហាញ 1 card គត់ */
                overflow-y: auto;  /* អនុញ្ញាតឲ្យ Scroll ចុះក្រោម */
                padding-right: 4px; /* ទុកចន្លោះ scrollbar */
                -webkit-overflow-scrolling: touch;
            }

            .order-card-item {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 12px 16px;
                background: #ffffff;
                flex-shrink: 0; /* ការពារកុំឲ្យ Card រួញតូច */
            }

            .order-card-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 6px 0;
                border-bottom: 1px dashed #f0f0f0;
            }

            .order-card-row:last-child {
                border-bottom: none;
            }

            .order-card-row .label {
                font-weight: 600;
                color: #475569;
                font-size: 0.88rem;
                text-align: left;
            }

            .order-card-row .value {
                font-weight: 500;
                color: #0f172a;
                font-size: 0.9rem;
                text-align: right;
            }
        }
    </style>
@endsection

@section('content')
    <div class="content-section active" id="dashboard">

        {{-- Flash Success Message --}}
        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif

        {{-- Welcome Banner --}}
        <div class="welcome-banner" style="margin-bottom: 20px; padding: 20px; background: #ffffff; border-left: 4px solid #030304; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
            <h2 style="margin: 0; color: #030304; display: flex; align-items: center; gap: 8px;">
                <span><i class="fas fa-hand-wave" style="color: #f39c12;"></i> Welcome back,</span>
                <span style="color: #ff5a05;">{{ Auth::user()->name ?? 'User' }}!</span>
            </h2>
        </div>

        <h3><i class="fas fa-chart-pie"></i> Dashboard Widgets & Analytics</h3><br>

        {{-- Sortable Container --}}
        <div class="charts-grid" id="sortableCharts">

            {{-- 1. Monthly Sales Chart --}}
            <div class="card">
                <h4><i class="fas fa-chart-line"></i> Monthly Sales</h4>
                <div class="chart-container">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            {{-- 2. Top Selling Products Chart --}}
            <div class="card">
                <h4><i class="fas fa-trophy"></i> Top Selling Products</h4>
                <div class="chart-container">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>

            {{-- 3. Recent Orders Table Card --}}
            <div class="card full-width">
                <h4 style="margin-bottom: 1rem;"><i class="fas fa-shopping-cart"></i> Recent Orders</h4>

                {{-- Desktop View --}}
                <div class="table-container desktop-table-view">
                    <table>
                        <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->customer->name ?? 'N/A' }}</td>
                                <td>{{ $order->product->name ?? 'N/A' }}</td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    @if($order->status==='pending')
                                        <i class="fas fa-hourglass-half" style="color: orange;"></i> Pending
                                    @elseif($order->status==='completed')
                                        <i class="fas fa-check-circle" style="color: green;"></i> Completed
                                    @else
                                        <i class="fas fa-times-circle" style="color: red;"></i> Cancelled
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;">No orders found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile View (Scrollable Single Card Display) --}}
                <div class="order-cards-mobile">
                    @forelse($recentOrders as $order)
                        <div class="order-card-item">
                            <div class="order-card-row">
                                <span class="label">Order ID</span>
                                <span class="value">{{ $order->order_number }}</span>
                            </div>
                            <div class="order-card-row">
                                <span class="label">Customer</span>
                                <span class="value">{{ $order->customer->name ?? 'N/A' }}</span>
                            </div>
                            <div class="order-card-row">
                                <span class="label">Product</span>
                                <span class="value">{{ $order->product->name ?? 'N/A' }}</span>
                            </div>
                            <div class="order-card-row">
                                <span class="label">Amount</span>
                                <span class="value">${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                            <div class="order-card-row">
                                <span class="label">Status</span>
                                <span class="value">
                                    @if($order->status==='pending')
                                        <i class="fas fa-hourglass-half" style="color: orange;"></i> Pending
                                    @elseif($order->status==='completed')
                                        <i class="fas fa-check-circle" style="color: green;"></i> Completed
                                    @else
                                        <i class="fas fa-times-circle" style="color: red;"></i> Cancelled
                                    @endif
                                </span>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; color: #64748b; padding: 1rem;">No orders found.</div>
                    @endforelse
                </div>

            </div>

            {{-- 4. Daily Sales Trends Chart --}}
            <div class="card full-width">
                <h4><i class="fas fa-chart-bar"></i> Daily Sales Trends (Amount vs Quantity)</h4>
                <div class="chart-container tall">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // Auto-hide alert message
            const msg = document.querySelector('.success-message');
            if (msg) { setTimeout(() => msg.remove(), 3000); }

            // Drag & Drop Setup
            const el = document.getElementById('sortableCharts');
            if (el) {
                new Sortable(el, {
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onStart: (evt) => evt.item.style.cursor = 'grabbing',
                    onEnd: (evt) => {
                        evt.item.style.cursor = 'grab';
                        window.dispatchEvent(new Event('resize'));
                    }
                });
            }

            // Top Products Chart
            new Chart(document.getElementById('topProductsChart'), {
                type: 'bar',
                data: {
                    labels: @json($topProducts->pluck('product.name')),
                    datasets: [{
                        label: 'Total Units Sold',
                        data: @json($topProducts->pluck('total_sold')),
                        backgroundColor: '#181c27',
                        borderRadius: 5
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });

            // Monthly Sales Chart
            new Chart(document.getElementById('lineChart'), {
                type: 'line',
                data: {
                    labels: @json($months),
                    datasets: [{
                        label: 'Monthly Sales ($)',
                        data: @json($monthlyRevenue),
                        borderColor: '#2980b9',
                        backgroundColor: '#181c27',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Daily Sales Trends Chart
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels ?? []) !!},
                    datasets: [
                        {
                            label: 'Total Amount ($)',
                            data: {!! json_encode($chartAmount ?? []) !!},
                            backgroundColor: '#181c27',
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Total Quantity (Qty)',
                            data: {!! json_encode($chartQty ?? []) !!},
                            backgroundColor: '#ff5a05',
                            borderRadius: 4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: { display: true, text: 'Day of Month' }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Amount ($)' },
                            beginAtZero: true
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: { display: true, text: 'Quantity (Qty)' },
                            beginAtZero: true,
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        });

        function showLoading(msg) {
            const ov = document.getElementById('loading-overlay');
            const lt = document.getElementById('loading-text');
            if (!ov) return;
            if (lt) lt.textContent = msg || 'Loading...';
            ov.style.display = 'flex';
        }
    </script>
@endsection
