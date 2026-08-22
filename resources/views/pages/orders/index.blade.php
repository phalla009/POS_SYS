@extends('layouts.master')

@section('pageTitle')
    Orders Listing
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/delete_form.css') }}">
    <script src="{{ URL::asset('js/form.js') }}"></script>
    <script src="{{ URL::asset('js/delete_form.js') }}"></script>

    <style>
        .status-pending   { color: orange; }
        .status-completed { color: green; }
        .status-cancelled { color: red; }
        .payment-btn.disabled { pointer-events: none; opacity: 0.5; cursor: default; }

        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fff; margin: 60px auto; padding: 20px; border-radius: 10px; width: 600px; height: 480px; position: relative; animation: fadeIn 0.3s forwards; overflow-y: auto; transform: scale(0.95); }
        @keyframes fadeIn { to { transform: scale(1); } }

        .btn-payment { margin-top: 20px; width: 100%; background: #48bb78; color: white; transition: 0.3s ease, transform 0.2s ease; padding: 10px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .btn-payment:hover { background: #38a169; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(56,161,105,0.3); }
        .close-modal { position: absolute; top: 10px; right: 15px; font-size: 24px; font-weight: bold; cursor: pointer; color: #333; }
        .close-modal:hover { color: #666; }
        .input-error { border: 1px solid red; }
        .error { color: red; margin-top: 4px; font-size: 0.9em; }

        .filter-section { margin-top: 0px; margin-bottom: 20px; width: 100%; }
        .filter-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .filter-form { display: flex; flex-direction: column; width: 100%; }

        /* Single row layout with flexbox */
        .filter-row { display: flex; flex-wrap: nowrap; align-items: flex-end; gap: 14px; width: 100%; }
        .filter-form .form-group { display: flex; flex-direction: column; gap: 4px; flex: 1; }
        .filter-form label { font-weight: 600; color: #333; }
        .filter-form select, .filter-form input[type="text"], .filter-form input[type="date"] { padding: 10px 14px; border: 1px solid #ccc; border-radius: 24px; font-size: 14px; outline-color: #3498db; box-shadow: 0 1px 2px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; }

        /* Filter / Clear buttons container */
        .filter-row-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .filter-form .btn { padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 999px; border: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; }
        .filter-form .btn-primary { background-color: #000; color: #fff; }
        .filter-form .btn-primary:hover { background-color: #262626; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.25); }
        .filter-form .btn-light { background-color: #fff; color: #c82333; border: 1.5px solid #c82333; }
        .filter-form .btn-light:hover { background-color: #c82333; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(200,35,51,0.25); }
        #filterBtn, #clearBtn { width: auto; }

        @media (max-width: 768px) { .filter-row { flex-direction: column; align-items: stretch; } .filter-row-actions { justify-content: flex-start; } }

        /* Dashboard stat cards */
        .stat-cards-row { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 14px; margin-top: 18px; width: 100%; box-sizing: border-box; }
        .stat-card {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
            padding: 20px 22px;
            border-radius: 8px;
            color: #fff;
            min-width: 0;
            overflow: hidden;
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }
        .stat-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.16), rgba(255,255,255,0) 55%);
            pointer-events: none;
        }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-card-icon {
            position: absolute;
            top: 14px;
            right: 16px;
            font-size: 15px;
            color: rgba(255,255,255,0.55);
        }
        .stat-card-label { font-size: 12.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.85); white-space: nowrap; }
        .stat-card-value { font-size: 22px; font-weight: 800; letter-spacing: -0.01em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .stat-card-orange   { background: repeating-linear-gradient(-45deg, #9a5e2d, #8c4f1e, #9a5e2d 3px, #8c4f1e 3px); box-shadow: 0 8px 20px -8px rgba(220,140,76,0.65); }
        .stat-card-green    { background: repeating-linear-gradient(-45deg, #57c088, #2f8f5c, #57c088 3px, #2f8f5c 3px); box-shadow: 0 8px 20px -8px rgba(47,143,92,0.65); }
        .stat-card-cyan     { background: repeating-linear-gradient(-45deg, #35d2ff, #00abdc, #35d2ff 3px, #00abdc 3px); box-shadow: 0 8px 20px -8px rgba(0,171,220,0.65); }
        .stat-card-magenta  { background: repeating-linear-gradient(-45deg, #dd35a8, #a3106e, #dd35a8 3px, #a3106e 3px); box-shadow: 0 8px 20px -8px rgba(163,16,110,0.65); }
        .stat-card-coral    { background: repeating-linear-gradient(-45deg, #dd8079, #b84f47, #dd8079 3px, #b84f47 3px); box-shadow: 0 8px 20px -8px rgba(184,79,71,0.65); }

        .stat-card:hover.stat-card-orange   { box-shadow: 0 14px 28px -10px rgba(220,140,76,0.75); }
        .stat-card:hover.stat-card-green    { box-shadow: 0 14px 28px -10px rgba(47,143,92,0.75); }
        .stat-card:hover.stat-card-cyan     { box-shadow: 0 14px 28px -10px rgba(0,171,220,0.75); }
        .stat-card:hover.stat-card-magenta  { box-shadow: 0 14px 28px -10px rgba(163,16,110,0.75); }
        .stat-card:hover.stat-card-coral    { box-shadow: 0 14px 28px -10px rgba(184,79,71,0.75); }

        @media (max-width: 900px) { .stat-cards-row { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 480px) { .stat-cards-row { grid-template-columns: 1fr; } }

        /* Bulk toolbar */
        #bulkToolbar { display: none; align-items: center; gap: 12px; margin-bottom: 12px; padding: 10px 16px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; animation: slideDown 0.2s ease; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        #bulkDeleteBtn { background: repeating-linear-gradient(-45deg, #c82333,#bd091b,#c82333 3px,#bd091b 3px); color: #fff; border: none; border-radius: 24px; padding: 8px 18px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background 0.2s, transform 0.15s; }
        #bulkDeleteBtn:hover { transform: translateY(-1px); }
        #bulkPrintBtn { background: #3498db; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background 0.2s, transform 0.15s; }
        #bulkPrintBtn:hover { background: #2980b9; transform: translateY(-1px); }
        #cancelSelectionBtn:hover { background: #ffcb0e; transform: translateY(-1px); }
        #cancelSelectionBtn { background: #ffcb0e; border: none; border-radius: 24px; color: #c82333; cursor: pointer; font-size: 13px; padding: 8px 18px; font-weight: 600;transition: background 0.2s, transform 0.15s;}
        tr.row-selected { background-color: #fff8e1 !important; }
        .row-checkbox, #selectAll { width: 16px; height: 16px; cursor: pointer; accent-color: #c82333; }

        /* Pagination */
        .orders-pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
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

    <div class="content-section" id="orders">
        <h2><i class="fas fa-shopping-cart"></i> Orders Management</h2>

        <div class="filter-section">
            <form method="GET" action="{{ route('orders.index') }}" class="filter-form" id="filterForm">
                <div class="filter-row">
                    <div class="form-group">
                        <label for="date_from">From Date</label>
                        <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}">
                    </div>
                    <div class="form-group">
                        <label for="date_to">To Date</label>
                        <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}">
                    </div>
                    <div class="filter-row-actions">
                        <button type="submit" class="btn btn-primary" id="filterBtn">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('orders.index') }}" class="btn btn-light page-link-loading" id="clearBtn" data-loading-text="Clearing...">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>

            <div class="stat-cards-row">
                <div class="stat-card stat-card-orange">
                    <i class="fas fa-file-invoice stat-card-icon"></i>
                    <span class="stat-card-label">Total Invoice</span>
                    <span class="stat-card-value">{{ number_format($totalInvoice) }}</span>
                </div>
                <div class="stat-card stat-card-green">
                    <i class="fas fa-boxes-stacked stat-card-icon"></i>
                    <span class="stat-card-label">Total Qty</span>
                    <span class="stat-card-value">{{ number_format($totalQty) }}</span>
                </div>
                <div class="stat-card stat-card-cyan">
                    <i class="fas fa-calendar-day stat-card-icon"></i>
                    <span class="stat-card-label">Daily Total</span>
                    <span class="stat-card-value">${{ number_format($dailyTotal, 2) }}</span>
                </div>
                <div class="stat-card stat-card-magenta">
                    <i class="fas fa-calendar-days stat-card-icon"></i>
                    <span class="stat-card-label">Monthly Total</span>
                    <span class="stat-card-value">${{ number_format($monthlyTotal, 2) }}</span>
                </div>
                <div class="stat-card stat-card-coral">
                    <i class="fas fa-crown stat-card-icon"></i>
                    <span class="stat-card-label">Grand Total</span>
                    <span class="stat-card-value">${{ number_format($grandTotal, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Bulk Toolbar (Admin Only) --}}
        @if(auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'Admin')
            <div id="bulkToolbar">
                <span id="selectedCount" style="font-weight:600;color:#856404;">0 selected</span>
                <button type="button" id="bulkDeleteBtn"><i class="fas fa-trash-alt"></i> Delete Selected</button>
                <button type="button" id="cancelSelectionBtn">Cancel</button>
            </div>
        @endif

        <div class="table-container">
            <table>
                <thead>
                <tr>
                    {{-- Check if user is Admin using the correct role() relation --}}
                    @if(auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'Admin')
                        <th style="width:40px;text-align:center;"><input type="checkbox" id="selectAll" title="Select All"></th>
                    @endif
                    <th>#</th>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Order Date</th>
                    <th>Paid Amount</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="ordersTable">
                @forelse ($orders as $order)
                    <tr>
                        @if(auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'Admin')
                            <td style="text-align:center;"><input type="checkbox" class="row-checkbox" value="{{ $order->id }}"></td>
                        @endif
                        <td data-label="No">#{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                        <td data-label="Order ID">{{ $order->order_number }}</td>
                        <td data-label="Product">{{ $order->product->name ?? 'N/A' }}</td>
                        <td data-label="Quantity">{{ $order->quantity }}</td>
                        <td data-label="Order Date">
                            {{ $order->created_at?->timezone('Asia/Phnom_Penh')->format('d M, Y h:i A') ?? 'N/A' }}
                        </td>
                        <td data-label="Paid Amount">${{ number_format($order->total_amount ?? 0, 2) }}</td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <a href="{{ route('orders.show', $order->id) }}"
                                   class="action-btn show-btn page-link-loading"
                                   data-loading-text="Loading details..." title="View Details">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;" id="found">No order found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($orders->hasPages())
            <nav aria-label="Page navigation" class="orders-pagination">
                <ul class="pagination-list">
                    @if ($orders->onFirstPage())
                        <li class="page-btn disabled"><span><i class="fa fa-angle-left"></i></span></li>
                    @else
                        <li class="page-btn">
                            <a href="{{ $orders->previousPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
                                <i class="fa fa-angle-left"></i>
                            </a>
                        </li>
                    @endif

                    @foreach ($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                        @if ($page == $orders->currentPage())
                            <li class="page-btn active"><span>{{ $page }}</span></li>
                        @else
                            <li class="page-btn">
                                <a href="{{ $url }}" class="page-link-loading" data-loading-text="Loading...">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    @if ($orders->hasMorePages())
                        <li class="page-btn">
                            <a href="{{ $orders->nextPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-btn disabled"><span><i class="fa fa-angle-right"></i></span></li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>

    {{-- Single Delete Modal --}}
    <x-delete-modal />

    {{-- Bulk Delete Modal (Admin Only) --}}
    @if(auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'Admin')
        <div id="bulkDeleteModal" class="modal">
            <div class="delete-modal-box">
                <div class="delete-modal-icon"><i class="fas fa-trash-alt"></i></div>
                <button class="delete-modal-close" id="bulkDeleteModalClose" aria-label="Close">&times;</button>
                <h3>Delete Selected Orders?</h3>
                <p>You are about to delete <strong id="bulkDeleteCount">0</strong> order(s). This action <strong>cannot be undone.</strong></p>
                <form id="bulkDeleteForm" method="POST" action="{{ route('orders.bulkDestroy') }}">
                    @csrf @method('DELETE')
                    <div id="bulkDeleteInputs"></div>
                    <div class="delete-modal-actions">
                        <button type="button" id="cancelBulkDelete" class="delete-btn-cancel"><i class="fas fa-times"></i> Cancel</button>
                        <button type="submit" class="delete-btn-confirm"><i class="fas fa-trash-alt"></i> Yes, Delete All</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        document.getElementById('filterForm').addEventListener('submit', function() {
            showLoading('Filtering...');
        });

        const selectAll         = document.getElementById('selectAll');
        const bulkToolbar       = document.getElementById('bulkToolbar');
        const selectedCount     = document.getElementById('selectedCount');
        const bulkDeleteModal   = document.getElementById('bulkDeleteModal');
        const bulkDeleteCount   = document.getElementById('bulkDeleteCount');
        const bulkDeleteInputs  = document.getElementById('bulkDeleteInputs');
        const bulkDeleteForm    = document.getElementById('bulkDeleteForm');

        function getChecked() { return Array.from(document.querySelectorAll('.row-checkbox:checked')); }

        function updateToolbar() {
            if (!bulkToolbar) return;
            const checked = getChecked(); const count = checked.length;
            const all = document.querySelectorAll('.row-checkbox');
            bulkToolbar.style.display = count > 0 ? 'flex' : 'none';
            if (count > 0) selectedCount.textContent = count + ' selected';
            if (selectAll) {
                selectAll.checked       = count === all.length && all.length > 0;
                selectAll.indeterminate = count > 0 && count < all.length;
            }
            document.querySelectorAll('.row-checkbox').forEach(function(cb) { cb.closest('tr').classList.toggle('row-selected', cb.checked); });
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(function(cb) { cb.checked = selectAll.checked; });
                updateToolbar();
            });
        }

        document.querySelectorAll('.row-checkbox').forEach(function(cb) { cb.addEventListener('change', updateToolbar); });

        const cancelSelectionBtn = document.getElementById('cancelSelectionBtn');
        if (cancelSelectionBtn) {
            cancelSelectionBtn.addEventListener('click', function() {
                document.querySelectorAll('.row-checkbox').forEach(function(cb) { cb.checked = false; });
                if (selectAll) { selectAll.checked = false; selectAll.indeterminate = false; }
                updateToolbar();
            });
        }

        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', function() {
                const checked = getChecked(); if (!checked.length) return;
                bulkDeleteCount.textContent = checked.length;
                bulkDeleteInputs.innerHTML = '';
                checked.forEach(function(cb) {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
                    bulkDeleteInputs.appendChild(inp);
                });
                bulkDeleteModal.style.display = 'flex';
            });
            document.getElementById('bulkDeleteModalClose').addEventListener('click', function() { bulkDeleteModal.style.display = 'none'; });
            document.getElementById('cancelBulkDelete').addEventListener('click', function() { bulkDeleteModal.style.display = 'none'; });
            bulkDeleteForm.addEventListener('submit', function() { showLoading('Deleting selected orders...'); });
        }
    </script>
@endsection
