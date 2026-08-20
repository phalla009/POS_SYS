@extends('layouts.master')

@section('pageTitle')
    Products Listing
@endsection

@section('headerBlock')
<link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
<link rel="stylesheet" href="{{ URL::asset('css/delete_form.css') }}">
<script src="{{ URL::asset('js/form.js') }}"></script>
<script src="{{ URL::asset('js/delete_form.js') }}" defer></script>
<style>
    .status-active   { color: green; background-color: transparent !important; }
    .status-inactive { color: red;   background-color: transparent !important; }
    .form-group select { border-radius: 24px; }

    /* Import modal */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.5);
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
        background: #fff;
        padding: 24px;
        border-radius: 10px;
        width: 420px;
        max-width: 90%;
        box-shadow: 0 10px 30px rgba(0,0,0,.2);
    }
    .modal-box h3 {
        margin: 0 0 16px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .modal-box .hint {
        font-size: 13px;
        color: #777;
        margin: 10px 0 0 0;
        line-height: 1.5;
    }
    .modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 18px;
    }
    .import-file-input {
        width: 100%;
        padding: 8px;
        border: 1px dashed #ccc;
        border-radius: 8px;
    }
    .import-errors {
        background: #fdecea;
        border: 1px solid #f5c2c0;
        color: #a12622;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
    }
    .btn-secondary-export{
        color: white;
        transition: all 0.2s ease;
        background: repeating-linear-gradient(
            -45deg,
            #ff2020,
            #d40f0f,
            #ff2020 3px,
            #d40f0f 3px
        );
    }
    .btn-secondary-export:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px #ff0000;
    }
    .btn-secondary-import{
        color: white;
        transition: all 0.2s ease;
        background: repeating-linear-gradient(
            -45deg,
            #0008ff,
            #0007ca,
            #0008ff 3px,
            #0007ca 3px
        );
    }
    .btn-secondary-import:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px #0008ff;
    }
    .import-errors ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }
    .filter-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-controls .btn {
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .filter-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-controls .btn,
        .filter-controls .form-group {
            width: 100%;
            max-width: 100%;
            margin-left: 0 !important;
        }

        .filter-controls a.btn,
        .filter-controls button.btn {
            justify-content: center;
            text-align: center;
        }

        .filter-controls .form-group[style*="min-width:300px"] {
            min-width: 100% !important;
        }

        .modal-box {
            width: 90%;
            padding: 16px;
        }

        .action-buttons {
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px;
        }
    }

    @media (max-width: 480px) {
        .btn i {
            margin-right: 4px;
        }

        .filter-controls .btn {
            font-size: 14px;
            padding: 8px 12px;
        }
    }

    /* Bulk delete */
    .bulk-actions-bar {
        display: none; align-items: center; gap: 10px; margin-bottom: 10px;
        background: #fff5f5; border: 1.5px solid #f5c2c2; border-radius: 10px; padding: 10px 14px;
    }
    .bulk-actions-bar.active { display: flex; }
    .bulk-actions-bar .selected-count { font-size: 13.5px; color: #c0392b; font-weight: 600; }
    .bulk-delete-btn {
        display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: #e74c3c; color: #fff;
        border: none; border-radius: 20px; font-size: 13.5px; cursor: pointer; transition: background 0.2s;
    }
    .bulk-delete-btn:hover { background: #c0392b; }
    .bulk-delete-btn:disabled { background: #f1a9a0; cursor: not-allowed; }
    .bulk-clear-btn {
        display: flex; align-items: center; gap: 5px; padding: 8px 14px; background: #f0f2f5; color: #666;
        border: 1.5px solid #dde3ec; border-radius: 20px; font-size: 13px; cursor: pointer; text-decoration: none;
    }
    .bulk-clear-btn:hover { background: #e8eaf0; }
    .row-checkbox, #selectAllCheckbox { width: 16px; height: 16px; cursor: pointer; accent-color: #e74c3c; }
    tr.row-selected { background: #fff7f7; }

    /* ── Delete Confirm Modal (popup delete form, matches Categories page) ── */
    /* ── Backdrop ── */
    .delete-confirm-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1100;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .delete-confirm-modal.open {
        display: flex;
    }

    /* ── Modal Box ── */
    .delete-modal-box {
        background: #ffffff;
        border-radius: 20px;
        max-width: 440px;
        width: 100%;
        padding: 40px 36px 32px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
        position: relative;
        text-align: center;
        animation: deleteModalIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        margin: auto;
    }

    /* ── Icon ── */
    .delete-modal-icon {
        width: 70px;
        height: 70px;
        background: #fff1f0;
        border: 2px solid #ffd6d4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .delete-modal-icon i {
        font-size: 28px;
        color: #e74c3c;
    }

    /* ── Close Button ── */
    .delete-modal-close {
        position: absolute;
        top: 16px;
        right: 18px;
        background: #f1f5f9;
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        font-size: 18px;
        color: #64748b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s, color 0.2s, transform 0.2s;
        line-height: 1;
    }

    .delete-modal-close:hover {
        background: #fee2e2;
        color: #e74c3c;
        transform: rotate(90deg);
    }

    /* ── Heading ── */
    .delete-modal-box h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 10px;
    }

    /* ── Body Text ── */
    .delete-modal-box p {
        font-size: 0.95rem;
        color: #64748b;
        margin-bottom: 28px;
        line-height: 1.6;
    }

    .delete-modal-box p strong {
        color: #e74c3c;
    }

    /* ── Actions ── */
    .delete-modal-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    /* ── Cancel Button ── */
    .delete-btn-cancel {
        padding: 10px 24px;
        border-radius: 24px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s, transform 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .delete-btn-cancel:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    /* ── Confirm Button ── */
    .delete-btn-confirm {
        padding: 10px 24px;
        border-radius: 24px;
        border: none;
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(231, 76, 60, 0.35);
    }

    .delete-btn-confirm:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(231, 76, 60, 0.45);
    }

    /* ── Animation ── */
    @keyframes deleteModalIn {
        from { transform: scale(0.88) translateY(16px); opacity: 0; }
        to   { transform: scale(1)    translateY(0);    opacity: 1; }
    }

    /* ── Responsive ── */
    @media (max-width: 480px) {
        .delete-modal-box {
            padding: 32px 20px 24px;
        }

        .delete-modal-actions {
            flex-direction: column;
        }

        .delete-btn-cancel,
        .delete-btn-confirm {
            width: 100%;
            justify-content: center;
        }
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

@if(session('error'))
<div class="import-errors">
    <strong>{{ session('error') }}</strong>
    @if(session('import_errors'))
        <ul>
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    @endif
</div>
@endif

<div class="content-section" id="products">
    <h2><i class="fas fa-box-open"></i> Products Management</h2>

    <div class="filter-section">
        <form id="filterForm" method="GET" action="{{ route('products.index') }}">
            <div class="filter-controls">
                <a href="{{ route('products.create') }}"
                    class="btn btn-primary page-link-loading"
                    data-loading-text="Loading form..."
                    style="white-space:nowrap;">
                    <i class="fas fa-circle-plus"></i> Add New Product
                </a>

                <button type="button" class="btn btn-secondary-import" style="white-space:nowrap;"
                        onclick="document.getElementById('importModal').classList.add('active')">
                    <i class="fas fa-file-excel"></i> Import Products
                </button>

                <a href="{{ route('products.export', request()->query()) }}"
                class="btn btn-secondary-export"
                style="white-space:nowrap;">
                    <i class="fas fa-file-export"></i> Export Products
                </a>

                <div class="form-group" style="max-width:200px;">
                    <select name="category_id" onchange="showLoading('Filtering...'); this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="position:relative; min-width:300px;">
                    <i class="fas fa-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#aaa;font-size:14px;pointer-events:none;"></i>
                    <input type="text" name="search"
                        value="{{ request('search') }}"
                        placeholder="Search Products..."
                        id="searchInput"
                        style="border-radius:24px; padding-left:38px;">
                </div>
            </div>
        </form>
    </div>

    {{-- Bulk actions bar --}}
    <div class="bulk-actions-bar" id="bulkActionsBar">
        <span class="selected-count" id="selectedCount">0 selected</span>
        <button type="button" class="bulk-delete-btn" id="bulkDeleteBtn" disabled>
            <i class="fas fa-trash"></i> Delete Selected
        </button>
        <button type="button" class="bulk-clear-btn" id="bulkClearBtn">
            <i class="fas fa-times"></i> Clear Selection
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width:36px;"><input type="checkbox" id="selectAllCheckbox" title="Select all"></th>
                    <th>#</th>
                    <th>Created At</th>
                    <th>Products</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="productsTable">
                @forelse ($products as $product)
                    <tr data-id="{{ $product->id }}">
                        <td data-label="Select">
                            <input type="checkbox" class="row-checkbox" value="{{ $product->id }}">
                        </td>
                        <td data-label="No">#{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
                        <td data-label="Create Date">
                            {{ $product->created_at?->timezone('Asia/Phnom_Penh')->format('d M, Y h:i A') ?? 'N/A' }}
                        </td>
                        <td data-label="Name">{{ $product->name }}</td>
                        <td data-label="Brand">{{ $product->category->name ?? 'N/A' }}</td>
                        <td data-label="Price">${{ number_format($product->price, 2) }}</td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <a href="{{ route('products.show', $product->id) }}"
                                   class="action-btn show-btn page-link-loading"
                                   data-loading-text="Loading details..."
                                   title="View Details">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('products.edit', $product->id) }}"
                                   class="action-btn edit-btn page-link-loading"
                                   data-loading-text="Loading editor..."
                                   title="Edit Product">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button"
                                    class="action-btn delete-btn openDeleteModal"
                                    data-action="{{ route('products.destroy', $product->id) }}"
                                    title="Delete Product">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;" id="found">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($products->hasPages())
    <nav aria-label="Page navigation" class="products-pagination">
        <ul class="pagination-list">
            @if ($products->onFirstPage())
                <li class="page-btn disabled">
                    <span><i class="fa fa-angle-left"></i></span>
                </li>
            @else
                <li class="page-btn">
                    <a href="{{ $products->previousPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
                        <i class="fa fa-angle-left"></i>
                    </a>
                </li>
            @endif

            @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                @if ($page == $products->currentPage())
                    <li class="page-btn active"><span>{{ $page }}</span></li>
                @else
                    <li class="page-btn">
                        <a href="{{ $url }}" class="page-link-loading" data-loading-text="Loading...">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            @if ($products->hasMorePages())
                <li class="page-btn">
                    <a href="{{ $products->nextPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
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

{{-- Delete Modal (single-item popup delete form) --}}
<x-delete-modal />

{{-- Bulk Delete Confirm Modal --}}
<x-delete-multiple-modal
    id="bulkDeleteConfirmModal"
    title="Delete Selected Products?"
>
    You are about to delete <strong><span id="bulkDeleteCountText">0</span> product<span id="bulkDeleteCountWord">s</span></strong>. This action cannot be undone.
</x-delete-multiple-modal>

{{-- Hidden form used to submit bulk delete --}}
<form id="bulkDeleteForm" action="{{ route('products.bulkDestroy') }}" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
    <div id="bulkDeleteIdsContainer"></div>
</form>

{{-- Import Excel Modal --}}
<div id="importModal" class="modal-overlay">
    <div class="modal-box">
        <h3><i class="fas fa-file-excel" style="color:#217346;"></i> Import Products</h3>

        <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="import-file-input" required>

            <p class="hint">
                Required columns: <strong>name, price, stock, category_id</strong><br>
                Optional columns: status, description, add_to_pos<br>
                @if(Route::has('products.import-template'))
                    <a href="{{ route('products.import-template') }}">Download sample template</a>
                @endif
            </p>

            <div class="modal-actions">
                <button type="submit" class="btn btn-secondary-import" onclick="showLoading('Importing...')">
                    <i class="fas fa-upload"></i> Upload
                </button>
                <button type="button" class="btn btn-cancel" onclick="document.getElementById('importModal').classList.remove('active')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Search Enter
    document.getElementById('searchInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') showLoading('Searching...');
    });

    // Reopen import modal automatically if there were import errors
    @if(session('error') && session('import_errors'))
        document.getElementById('importModal').classList.add('active');
    @endif

    // Close modal when clicking outside the box
    document.getElementById('importModal').addEventListener('click', function (e) {
        if (e.target === this) this.classList.remove('active');
    });

    // ---- Bulk select / delete ----
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const rowCheckboxes = () => Array.from(document.querySelectorAll('.row-checkbox'));
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountEl = document.getElementById('selectedCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkClearBtn = document.getElementById('bulkClearBtn');
    const bulkDeleteModal = document.getElementById('bulkDeleteConfirmModal');
    const bulkDeleteCancelBtn = document.getElementById('bulkDeleteConfirmModalCancelBtn');
    const bulkDeleteCloseBtn = document.getElementById('bulkDeleteConfirmModalCloseBtn');
    const bulkDeleteConfirmBtn = document.getElementById('bulkDeleteConfirmModalConfirmBtn');
    const bulkDeleteCountText = document.getElementById('bulkDeleteCountText');
    const bulkDeleteCountWord = document.getElementById('bulkDeleteCountWord');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkDeleteIdsContainer = document.getElementById('bulkDeleteIdsContainer');

    function getSelectedIds() {
        return rowCheckboxes().filter(cb => cb.checked).map(cb => cb.value);
    }

    function updateBulkUI() {
        const checked = rowCheckboxes().filter(cb => cb.checked);
        const count = checked.length;

        rowCheckboxes().forEach(cb => {
            const row = cb.closest('tr');
            if (row) row.classList.toggle('row-selected', cb.checked);
        });

        bulkActionsBar.classList.toggle('active', count > 0);
        selectedCountEl.textContent = count + ' selected';
        bulkDeleteBtn.disabled = count === 0;

        if (selectAllCheckbox) {
            const total = rowCheckboxes().length;
            selectAllCheckbox.checked = total > 0 && count === total;
            selectAllCheckbox.indeterminate = count > 0 && count < total;
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes().forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateBulkUI();
        });
    }

    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('row-checkbox')) {
            updateBulkUI();
        }
    });

    if (bulkClearBtn) {
        bulkClearBtn.addEventListener('click', function() {
            rowCheckboxes().forEach(cb => cb.checked = false);
            updateBulkUI();
        });
    }

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const ids = getSelectedIds();
            if (ids.length === 0) return;
            bulkDeleteCountText.textContent = ids.length;
            bulkDeleteCountWord.textContent = ids.length === 1 ? '' : 's';
            bulkDeleteModal.classList.add('open');
        });
    }

    function closeBulkDeleteModal() {
        bulkDeleteModal.classList.remove('open');
    }

    if (bulkDeleteCancelBtn) {
        bulkDeleteCancelBtn.addEventListener('click', closeBulkDeleteModal);
    }

    if (bulkDeleteCloseBtn) {
        bulkDeleteCloseBtn.addEventListener('click', closeBulkDeleteModal);
    }

    if (bulkDeleteModal) {
        bulkDeleteModal.addEventListener('click', function(e) {
            if (e.target === bulkDeleteModal) closeBulkDeleteModal();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && bulkDeleteModal.classList.contains('open')) {
            closeBulkDeleteModal();
        }
    });

    if (bulkDeleteConfirmBtn) {
        bulkDeleteConfirmBtn.addEventListener('click', function() {
            const ids = getSelectedIds();
            if (ids.length === 0) return;

            bulkDeleteIdsContainer.innerHTML = '';
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                bulkDeleteIdsContainer.appendChild(input);
            });

            bulkDeleteForm.submit();
        });
    }

    updateBulkUI();
</script>
@endsection