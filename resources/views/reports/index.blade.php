@extends('layouts.master')

@section('pageTitle')
    Reports Listing
@endsection

@section('headerBlock')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- បន្ថែម SortableJS CDN សម្រាប់មុខងារ Drag & Drop --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <script src="{{ URL::asset('js/report.js') }}"></script>

    <style>
        /* កែសម្រួល CSS សម្រាប់ Stat Cards ឱ្យអាចអូសទាញបាន */
        .stats-grid {
            cursor: default;
        }
        .stats-grid .stat-card {
            cursor: grab !important;
            user-select: none;
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }
        .stats-grid .stat-card:active {
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
    <div class="content-section" id="report">
        <h2><i class="fas fa-chart-line"></i> Reports & Analytics</h2><br>
        <div class="stats-grid" id="sortableCards">
            <div class="stat-card">
                <h3>${{ number_format($salesThisMonth ?? 0, 2) }}</h3>
                <p>This Month Sales</p>
            </div>
            <div class="stat-card">
                <h3>{{ $ordersThisMonth ?? 0 }}</h3>
                <p>Orders This Month</p>
            </div>
            <div class="stat-card">
                <h3>{{ $newCustomers ?? 0 }}</h3>
                <p>New Customers</p>
            </div>
            <div class="stat-card">
                <h3>{{ $customerSatisfaction ?? 0 }}%</h3>
                <p>Customer Satisfaction</p>
            </div>
        </div>

        <form id="reportForm" method="POST" action="{{ route('reports.generate') }}" style="margin-top: 20px;">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label>Report Type:</label>
                    <select name="type" required class="form-control">
                        <option value="sales">Sales Report</option>
                        {{--                    <option value="inventory">Inventory Report</option>--}}
                        {{--                    <option value="customer">Customer Report</option>--}}
                        {{--                    <option value="financial">Financial Report</option>--}}
                    </select>
                </div>
                <div class="form-group">
                    <label>Date Range:</label>
                    <select name="range">
                        <option value="today">Today</option>
                        <option value="7days">Last 7 Days</option>
                        <option value="30days">Last 30 Days</option>
                        <option value="3months">Last 3 Months</option>
                        <option value="1year">Last Year</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-chart-pie"></i> Generate Report
            </button>
        </form>

        <div style="margin-top: 2rem; background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3>Daily Sales Trends</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // ===== Enable Drag & Drop for Stat Cards =====
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('sortableCards');
            if (el) {
                new Sortable(el, {
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onStart: function (evt) {
                        evt.item.style.cursor = 'grabbing';
                    },
                    onEnd: function (evt) {
                        evt.item.style.cursor = 'grab';
                    }
                });
            }
        });

        // ✅ reuse overlay ពី master layout
        function showLoading(msg) {
            const ov = document.getElementById('loading-overlay');
            const lt = document.getElementById('loading-text');
            if (!ov) return;
            if (lt) lt.textContent = msg || 'Loading...';
            ov.style.display = 'flex';
        }

        // Report form submit → show loading
        document.getElementById('reportForm').addEventListener('submit', function() {
            showLoading('Generating report...');
        });

        // Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels ?? []) !!},
                datasets: [{
                    label: 'Monthly Revenue ($)',
                    data: {!! json_encode($chartData ?? []) !!},
                    borderColor: 'rgb(82, 167, 232)',
                    backgroundColor: '#181c27',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { title: { display: true, text: 'Date' } },
                    y: { title: { display: true, text: 'Revenue ($)' }, beginAtZero: true }
                }
            }
        });
    </script>

@endsection
