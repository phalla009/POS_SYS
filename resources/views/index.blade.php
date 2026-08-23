@extends('layouts.master')

@section('pageTitle') Dashboard @endsection

@section('headerBlock')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- SortableJS CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/dashboard.css') }}">

    <style>
        /* កែសម្រួល CSS ទីតាំង និងទំហំកាតដើម្បីងាយស្រួលទាញ */
        .charts-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .charts-grid .card {
            flex: 1;
            min-width: 300px;
            cursor: grab !important;
            user-select: none; /* ការពារមិនឱ្យអូសโดន highlight អក្សរ */
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
    </style>
@endsection

@section('content')
    <div class="content-section active" id="dashboard">

        {{-- Success message --}}
        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif

        {{-- Welcome User Section with Box Shadow --}}
        <div class="welcome-banner" style="margin-bottom: 20px; padding: 20px; background: #ffffff; border-left: 4px solid #030304; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
            <h2 style="margin: 0; color: #030304; display: flex; align-items: center; gap: 8px;">
                <span><i class="fas fa-hand-wave" style="color: #f39c12;"></i> Welcome back,</span>
                <span style="color: #ff5a05;">{{ Auth::user()->name ?? 'User' }}!</span>
            </h2>
        </div>

        <h3><i class="fas fa-chart-pie"></i> Sales Analysis & Ranking</h3><br>

        {{-- Charts Grid អាចទាញប្តូរឆ្វេងស្តាំ (Drag & Drop) បាន --}}
        <div class="charts-grid" id="sortableCharts">
            <div class="card">
                <h4><i class="fas fa-chart-line"></i> Monthly Sales</h4>
                <canvas id="lineChart"></canvas>
            </div>

            <div class="card">
                <h4><i class="fas fa-trophy"></i> Top Selling Products</h4>
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>

        <div class="table-container">
            <h3><i class="fas fa-shopping-cart"></i> Recent Orders</h3><br>
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
                        <td>${{ number_format($order->total_amount,2) }}</td>
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
    </div>

    <script>
        // ===== Enable Drag & Drop for Charts Grid =====
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('sortableCharts');
            if (el) {
                new Sortable(el, {
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    // បន្ថែមការផ្អាក Chart.js បន្តិចពេលកំពុងទាញ ដើម្បីកុំឱ្យរអាក់រអួល
                    onStart: function (evt) {
                        evt.item.style.cursor = 'grabbing';
                    },
                    onEnd: function (evt) {
                        evt.item.style.cursor = 'grab';
                        // Refresh charts size after moving
                        window.dispatchEvent(new Event('resize'));
                    }
                });
            }
        });

        // ===== Top 10 Selling Products Chart =====
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
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // ===== Monthly Sales =====
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: @json($months),
                datasets: [{
                    label: 'Monthly Sales ($)',
                    data: @json($monthlyRevenue),
                    borderColor:'#2980b9',
                    backgroundColor:'#181c27',
                    fill:true,
                    tension:0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });

        // ===== Auto-hide success message =====
        document.addEventListener('DOMContentLoaded', () => {
            const msg = document.querySelector('.success-message');
            if(msg){ setTimeout(()=>msg.remove(),3000); }
        });
    </script>
@endsection
