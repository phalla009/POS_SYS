@extends('layouts.master')

@section('pageTitle')
    Transactions
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .stat-card { cursor: grab !important; user-select: none; word-break: break-word; }
        .stat-card:active { cursor: grabbing !important; }
        .sortable-ghost { opacity: 0.3; background: #f0f0f0; border: 2px dashed #030304; }
        .sortable-drag { background: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.15); }

        .filter-bar {
            display: flex; flex-wrap: wrap; gap: 10px;
            margin: 16px 0 12px 0; align-items: center;
        }
        .filter-bar input, .filter-bar select {
            padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px;
            font-size: 14px; background: #fff;
        }
        .filter-bar input[type="text"] { min-width: 220px; }

        .table-container { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .inventory-pagination { display: flex; justify-content: center; margin-top: 20px; }
        .amount-in  { color: #2e7d32; font-weight: 600; }
        .amount-out { color: #dc2626; font-weight: 600; }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 12px; }
            .filter-bar input[type="text"] { min-width: 100%; }
        }
        @media (max-width: 480px) {
            .stats-grid { gap: 10px; }
            .stat-card { padding: 12px 10px; }
            .stat-card h3 { font-size: 1.2rem; margin-bottom: 4px; }
            .stat-card p { font-size: 11px; }
        }
    </style>
@endsection

@section('content')
    @if(session('success'))
        <div id="successMessage" class="custom-success">
            <div class="success-content">
                <span class="success-icon">✔</span>
                <span class="success-text">{{ session('success') }}</span>
            </div>
            <div class="progress-bar"></div>
        </div>
    @endif

    <div class="content-section" id="transactions">
        <h2><i class="fas fa-receipt"></i> Transactions</h2>

        {{-- Stats --}}
        <div class="stats-grid" id="sortableCards" style="margin-top: 16px;">
            <div class="stat-card">
                <p>Total Transactions</p>
                <h3>{{ number_format($totalTransactions) }}</h3>
            </div>
            <div class="stat-card">
                <p>Total Income</p>
                <h3>${{ number_format($totalIncome, 2) }}</h3>
            </div>
            <div class="stat-card">
                <p>Total Expense</p>
                <h3>${{ number_format($totalExpense, 2) }}</h3>
            </div>
            <div class="stat-card">
                <p>Net Balance</p>
                <h3>{{ $netBalance < 0 ? '-' : '' }}${{ number_format(abs($netBalance), 2) }}</h3>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('transactions.index') }}" class="filter-bar">
            <select name="type">
                <option value="">All Types</option>
                <option value="income"  @selected(request('type') === 'income')>Income (Orders)</option>
                <option value="expense" @selected(request('type') === 'expense')>Expense (Inventory)</option>
            </select>
            <input type="date" name="date_from" value="{{ $dateFrom }}" title="From date">
            <input type="date" name="date_to"   value="{{ $dateTo }}"   title="To date">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('transactions.index') }}" class="btn"><i class="fas fa-rotate-left"></i> Today</a>
        </form>

        {{-- Daily summary table --}}
        <div class="table-container" role="region" aria-label="Daily transactions table">
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Orders</th>
                    <th>Inventory Items</th>
                    <th>Income</th>
                    <th>Expense</th>
                    <th>Net</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($transactions as $d)
                    <tr>
                        <td data-label="No">#{{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}</td>
                        <td data-label="Date" style="color: black; font-weight: 600;">
                            {{ \Carbon\Carbon::parse($d['date'])->format('d M, Y') }}
                        </td>
                        <td data-label="Orders">{{ $d['orders'] }}</td>
                        <td data-label="Inventory Items">{{ $d['items'] }}</td>
                        <td data-label="Income" class="amount-in">+${{ number_format($d['income'], 2) }}</td>
                        <td data-label="Expense" class="amount-out">-${{ number_format($d['expense'], 2) }}</td>
                        <td data-label="Net" style="font-weight:700; color: {{ $d['net'] < 0 ? '#dc2626' : '#2e7d32' }};">
                            {{ $d['net'] < 0 ? '-' : '' }}${{ number_format(abs($d['net']), 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;" id="found">No transactions found.</td>
                    </tr>
                @endforelse
                </tbody>
                @if($transactions->count() > 0)
                    <tfoot>
                    <tr style="background:#f1f5f9; font-weight:800;">
                        <td colspan="4" style="text-align:right;">Grand Total</td>
                        <td class="amount-in">+${{ number_format($totalIncome, 2) }}</td>
                        <td class="amount-out">-${{ number_format($totalExpense, 2) }}</td>
                        <td style="color: {{ $netBalance < 0 ? '#dc2626' : '#2e7d32' }};">
                            {{ $netBalance < 0 ? '-' : '' }}${{ number_format(abs($netBalance), 2) }}
                        </td>
                    </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Pagination --}}
        @if ($transactions->hasPages())
            <nav aria-label="Page navigation" class="inventory-pagination">
                <ul class="pagination-list">
                    @if ($transactions->onFirstPage())
                        <li class="page-btn disabled"><span><i class="fa fa-angle-left"></i></span></li>
                    @else
                        <li class="page-btn">
                            <a href="{{ $transactions->previousPageUrl() }}" class="page-link-loading" data-loading-text="Loading..."><i class="fa fa-angle-left"></i></a>
                        </li>
                    @endif

                    @foreach ($transactions->getUrlRange(1, $transactions->lastPage()) as $page => $url)
                        @if ($page == $transactions->currentPage())
                            <li class="page-btn active"><span>{{ $page }}</span></li>
                        @else
                            <li class="page-btn">
                                <a href="{{ $url }}" class="page-link-loading" data-loading-text="Loading...">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    @if ($transactions->hasMorePages())
                        <li class="page-btn">
                            <a href="{{ $transactions->nextPageUrl() }}" class="page-link-loading" data-loading-text="Loading..."><i class="fa fa-angle-right"></i></a>
                        </li>
                    @else
                        <li class="page-btn disabled"><span><i class="fa fa-angle-right"></i></span></li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('sortableCards');
            if (el) {
                new Sortable(el, {
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag'
                });
            }
        });

        function showLoading(msg) {
            const ov = document.getElementById('loading-overlay');
            const lt = document.getElementById('loading-text');
            if (!ov) return;
            if (lt) lt.textContent = msg || 'Loading...';
            ov.style.display = 'flex';
        }

        document.querySelectorAll('.page-link-loading').forEach(function (link) {
            link.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                const msg  = this.getAttribute('data-loading-text') || 'Loading...';
                if (href && href !== '#' && href !== 'javascript:void(0)') {
                    e.preventDefault();
                    showLoading(msg);
                    window.location.href = href;
                }
            });
        });
    </script>
@endsection
