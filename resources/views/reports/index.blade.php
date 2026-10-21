@extends('layouts.master')

@section('pageTitle')
    Reports Listing
@endsection

@section('headerBlock')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- SortableJS CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <script src="{{ URL::asset('js/report.js') }}"></script>

    <style>
        /* Base Grid Layout */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            cursor: default;
        }

        /* Stat Cards Drag & Drop Styles */
        .stats-grid .stat-card {
            cursor: grab !important;
            user-select: none;
            transition: transform 0.22s ease, box-shadow 0.22s ease;
            word-break: break-word;
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

        /* Form Row: 1 row, 3 columns (Report Type | From Date | To Date) */
        .form-row-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .form-row-3 .form-group {
            min-width: 0;
        }
        .form-row-3 .form-control {
            width: 100%;
            box-sizing: border-box;
            border-radius: 50px;
        }

        /* ── Mobile & Tablet 2-Column Responsive Rules ── */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 12px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 10px;
            }

            .stats-grid .stat-card {
                padding: 12px 10px;
            }

            .stats-grid .stat-card h3 {
                font-size: 1.2rem;
                margin-bottom: 4px;
            }

            .stats-grid .stat-card p {
                font-size: 11px;
            }

            /* Responsive Form Layout (stays 3 columns, tighter spacing) */
            .form-row-3 {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }

            .form-row-3 label {
                font-size: 12px;
            }

            .form-row-3 .form-control {
                font-size: 12px;
                padding: 6px 4px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="content-section" id="report">
        <h2><i class="fas fa-chart-line"></i> Reports & Analytics</h2><br>

        {{-- Stat Cards Grid (2-Column Grid on Mobile) --}}
        <div class="stats-grid" id="sortableCards">
            <div class="stat-card">
                <p>Total Monthly Sales</p>
                <h3>${{ number_format($salesThisMonth ?? 0, 2) }}</h3>
            </div>
            <div class="stat-card">
                <p>Orders This Month</p>
                <h3>{{ $ordersThisMonth ?? 0 }}</h3>
            </div>
            <div class="stat-card">
                <p>Total Inventory</p>
                <h3>${{ number_format($totalInventory ?? 0, 2) }}</h3>
            </div>
            <div class="stat-card">
                <p>Net</p>
                <h3 style="color: {{ ($net ?? 0) >= 0 ? '#ffffff' : '#dc2626' }}">
                    ${{ number_format($net ?? 0, 2) }}
                </h3>
            </div>
        </div>

        <form id="reportForm" method="POST" action="{{ route('reports.generate') }}" style="margin-top: 20px;">
            @csrf
            <div class="form-row-3">
                <div class="form-group">
                    <label>Report Type:</label>
                    <select name="type" required class="form-control">
                        <option value="sales">Sales Report</option>
                        <option value="financial">Financial Report</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>From Date:</label>
                    <input type="date" name="from_date" id="from_date" class="form-control"
                           value="{{ old('from_date', now()->startOfMonth()->toDateString()) }}"
                           max="{{ now()->toDateString() }}" required>
                </div>
                <div class="form-group">
                    <label>To Date:</label>
                    <input type="date" name="to_date" id="to_date" class="form-control"
                           value="{{ old('to_date', now()->toDateString()) }}"
                           max="{{ now()->toDateString() }}" required>
                </div>
            </div>

            @if ($errors->any())
                <div style="color: #c0392b; margin-top: 8px;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-chart-pie"></i> Generate Report
            </button>
        </form>

        {{-- Daily Sales Trends (2 Columns Bar Chart: Amount vs Qty) --}}
        <div style="margin-top: 2rem; background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3>Daily Sales Trends (Amount vs Quantity)</h3>
            <div style="position: relative; height: 320px; width: 100%;">
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

        // ===== From / To date: "To" can never be earlier than "From" =====
        document.addEventListener('DOMContentLoaded', () => {
            const fromDate = document.getElementById('from_date');
            const toDate   = document.getElementById('to_date');
            if (!fromDate || !toDate) return;

            toDate.min = fromDate.value;

            fromDate.addEventListener('change', () => {
                toDate.min = fromDate.value;
                if (toDate.value && toDate.value < fromDate.value) {
                    toDate.value = fromDate.value;
                }
            });
        });

        // ✅ Overlay reusable ពី Master Layout
        function showLoading(msg) {
            const ov = document.getElementById('loading-overlay');
            const lt = document.getElementById('loading-text');
            if (!ov) return;
            if (lt) lt.textContent = msg || 'Loading...';
            ov.style.display = 'flex';
        }

        // Report form submit → show loading
        const reportForm = document.getElementById('reportForm');
        if (reportForm) {
            reportForm.addEventListener('submit', function() {
                showLoading('Generating report...');
            });
        }

        // ===== Bar Chart 2 Columns (Amount & Qty Side-by-Side) =====
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
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
                        title: { display: true, text: 'Date' }
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
                        grid: { drawOnChartArea: false } // ការពារកុំឱ្យ Overlap Lines លើ Grid
                    }
                }
            }
        });
    </script>
@endsection
