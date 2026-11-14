@extends('layouts.master')

@section('pageTitle')
    Categories Listing
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/delete_form.css') }}">
    <script src="{{ URL::asset('js/form.js') }}" defer></script>
    <script src="{{ URL::asset('js/delete_form.js') }}" defer></script>

    <style>
        /* =============================================
           PAGE COLORS (edit here to re-theme this page)
        ============================================= */
        :root {
            /* Neutrals */
            --c-white: #fff;
            --c-text: #333;
            --c-text-mid: #666;
            --c-placeholder: #bbb;
            --c-icon: #aaa;
            --c-empty-icon: #ccc;
            --c-black: black;
            --c-search-icon: #b39b85;

            /* Inputs / surfaces */
            --c-input-bg: #f9fafc;
            --c-input-border: #dde3ec;
            --c-btn-dark: #282828;
            --c-btn-dark-hover: #333;
            --c-reset-bg: #f0f2f5;
            --c-reset-hover: #e8eaf0;

            /* Blue (focus) */
            --c-blue: #3498db;
            --c-blue-text: #2980b9;
            --c-blue-bg: #eaf4fd;
            --c-blue-border: #b6d9f5;

            /* Green (active) */
            --c-green: #27ae60;
            --c-green-bg: #eafaf1;
            --c-green-border: #b7ecc9;

            /* Red (inactive / delete) */
            --c-red: #e74c3c;
            --c-red-d: #c0392b;
            --c-red-bg: #fdf1f0;
            --c-red-border: #f5c2c2;
            --c-red-disabled: #f1a9a0;
            --c-bulk-bg: #fff5f5;
            --c-row-selected: #fff7f7;
            --c-red-soft-bg: #fff1f0;
            --c-red-soft-border: #ffd6d4;
            --c-red-close-bg: #fee2e2;

            /* Slate (modal / mobile cards) */
            --c-slate-900: #1e293b;
            --c-slate-600: #475569;
            --c-slate-500: #64748b;
            --c-slate-300: #cbd5e1;
            --c-slate-200: #e2e8f0;
            --c-slate-100: #f1f5f9;
            --c-slate-50: #f8fafc;
            --c-dash: #edf2f7;
        }

        /* Filter row (same layout as products page) */
        .filter-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-controls .btn {
            flex-shrink: 0;
        }

        /* Status badge in table */
        .status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 600;
            text-transform: capitalize;
        }
        .status-badge.status-active { background: var(--c-green-bg); color: var(--c-green); border: 1px solid var(--c-green-border); }
        .status-badge.status-inactive { background: var(--c-red-bg); color: var(--c-red); border: 1px solid var(--c-red-border); }
        .status-badge i { font-size: 8px; }

        /* Bulk delete */
        .bulk-actions-bar {
            display: none; align-items: center; gap: 10px; margin-bottom: 10px;
            background: var(--c-bulk-bg); border: 1.5px solid var(--c-red-border); border-radius: 10px; padding: 10px 14px;
        }
        .bulk-actions-bar.active { display: flex; }
        .bulk-actions-bar .selected-count { font-size: 13.5px; color: var(--c-red-d); font-weight: 600; }
        .bulk-delete-btn {
            display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--c-red); color: var(--c-white);
            border: none; border-radius: 20px; font-size: 13.5px; cursor: pointer; transition: background 0.2s;
        }
        .bulk-delete-btn:hover { background: var(--c-red-d); }
        .bulk-delete-btn:disabled { background: var(--c-red-disabled); cursor: not-allowed; }
        .bulk-clear-btn {
            display: flex; align-items: center; gap: 5px; padding: 8px 14px; background: var(--c-reset-bg); color: var(--c-text-mid);
            border: 1.5px solid var(--c-input-border); border-radius: 20px; font-size: 13px; cursor: pointer; text-decoration: none;
        }
        .bulk-clear-btn:hover { background: var(--c-reset-hover); }
        .row-checkbox, #selectAllCheckbox { width: 16px; height: 16px; cursor: pointer; accent-color: var(--c-red); }
        tr.row-selected { background: var(--c-row-selected); }

        /* ── Delete Confirm Modal ── */
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
            background: var(--c-white);
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
            background: var(--c-red-soft-bg);
            border: 2px solid var(--c-red-soft-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .delete-modal-icon i {
            font-size: 28px;
            color: var(--c-red);
        }

        /* ── Close Button ── */
        .delete-modal-close {
            position: absolute;
            top: 16px;
            right: 18px;
            background: var(--c-slate-100);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            font-size: 18px;
            color: var(--c-slate-500);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, color 0.2s, transform 0.2s;
            line-height: 1;
        }

        .delete-modal-close:hover {
            background: var(--c-red-close-bg);
            color: var(--c-red);
            transform: rotate(90deg);
        }

        /* ── Heading ── */
        .delete-modal-box h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--c-slate-900);
            margin-bottom: 10px;
        }

        /* ── Body Text ── */
        .delete-modal-box p {
            font-size: 0.95rem;
            color: var(--c-slate-500);
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .delete-modal-box p strong {
            color: var(--c-red);
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
            border: 1.5px solid var(--c-slate-200);
            background: var(--c-slate-50);
            color: var(--c-slate-600);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s, transform 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .delete-btn-cancel:hover {
            background: var(--c-slate-100);
            border-color: var(--c-slate-300);
            transform: translateY(-1px);
        }

        /* ── Confirm Button ── */
        .delete-btn-confirm {
            padding: 10px 24px;
            border-radius: 24px;
            border: none;
            background: linear-gradient(135deg, var(--c-red), var(--c-red-d));
            color: var(--c-white);
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

        /* Pagination */
        .categories-pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        /* ── Responsive Enhancements ── */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Tablet & Mobile Layout (<= 768px) */
        @media (max-width: 768px) {
            .filter-section .filter-controls {
                flex-direction: column;
                align-items: stretch !important;
                gap: 10px;
            }

            .filter-controls .btn-primary {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .filter-controls form,
            .filter-controls .form-group {
                width: 100%;
                min-width: 100% !important;
            }

            .bulk-actions-bar.active {
                flex-wrap: wrap;
                gap: 8px;
            }

            .bulk-delete-btn,
            .bulk-clear-btn {
                flex: 1;
                justify-content: center;
            }

            .selected-count {
                width: 100%;
                text-align: center;
                margin-bottom: 2px;
            }
        }

        /* Mobile Stacked Table Cards (<= 576px) */
        @media (max-width: 576px) {
            .table-container table,
            .table-container thead,
            .table-container tbody,
            .table-container th,
            .table-container td,
            .table-container tr {
                display: block;
            }

            .table-container thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            .table-container tr {
                background: var(--c-white);
                border: 1px solid var(--c-slate-200);
                border-radius: 12px;
                margin-bottom: 12px;
                padding: 12px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            }

            .table-container tr.row-selected {
                border-color: var(--c-red-border);
            }

            .table-container td {
                border: none;
                position: relative;
                padding: 8px 0 8px 45% !important;
                text-align: right !important;
                display: flex;
                align-items: center;
                justify-content: flex-end;
                min-height: 36px;
            }

            .table-container td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 40%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: 600;
                color: var(--c-slate-500);
                font-size: 13px;
            }

            .table-container td[data-label="Select"] {
                padding-left: 0 !important;
                justify-content: flex-start;
            }

            .table-container td[data-label="Select"]::before {
                display: none;
            }

            .table-container td[data-label="Actions"] {
                padding-left: 0 !important;
                justify-content: flex-end;
                margin-top: 6px;
                border-top: 1px dashed var(--c-dash);
                padding-top: 10px !important;
            }

            .table-container td[data-label="Actions"]::before {
                display: none;
            }

            .table-container td#found {
                padding: 20px !important;
                text-align: center !important;
            }

            .table-container td#found::before {
                display: none;
            }

            .delete-modal-box {
                padding: 24px 18px 20px;
            }

            .delete-modal-actions {
                flex-direction: column;
                width: 100%;
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

    <div class="content-section" id="categories">
        <h2><i class="fas fa-tags"></i> Categories Management</h2>

        <div class="filter-section">
            <div class="filter-controls">

                {{-- Add --}}
                <a href="{{ route('categories.create') }}"
                   class="btn btn-primary page-link-loading"
                   data-loading-text="Loading form..."
                   style="white-space:nowrap;">
                    <i class="fas fa-circle-plus"></i> Add New Category
                </a>

                {{-- Search (same style as products page) --}}
                <form method="GET" action="{{ route('categories.index') }}" id="searchForm" style="margin:0;">
                    <div class="form-group" style="position:relative; min-width:800px; margin:0;">
                        <i class="fas fa-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--c-search-icon);font-size:14px;pointer-events:none;"></i>
                        <input type="text" name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Search Categories..."
                               id="searchInput"
                               style="border-radius:24px; padding-left:38px;">
                    </div>
                </form>

            </div>
        </div>

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
            <div class="table-responsive">
                <table>
                    <thead>
                    <tr>
                        <th style="width:36px;">#</th>
                        <th style="width:36px;"><input type="checkbox" id="selectAllCheckbox" title="Select all"></th>
                        <th>Category Name</th>
                        <th>Status</th>
                        <th>Last Update</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="categoriesTable">
                    @forelse ($categories as $category)
                        <tr data-id="{{ $category->id }}">
                            <td data-label="No">#{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                            <td data-label="Select">
                                <input type="checkbox" class="row-checkbox" value="{{ $category->id }}">
                            </td>
                            <td data-label="Category Name" style="color: var(--c-black); font-weight: 600;">{{ $category->name }}</td>
                            <td data-label="Status">
                                <span class="status-badge status-{{ $category->status }}">
                                    <i class="fas fa-circle"></i> {{ $category->status }}
                                </span>
                            </td>
                            <td data-label="Created At">{{ $category->updated_at ? $category->updated_at->format('Y-M-d \a\t h:i A') : 'N/A' }}</td>
                            <td data-label="Actions">
                                <div class="action-buttons">
                                    <a href="{{ route('categories.show', $category->id) }}"
                                       class="action-btn show-btn page-link-loading"
                                       data-loading-text="Loading details..."
                                       title="View Details">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                    <a href="{{ route('categories.edit', $category->id) }}"
                                       class="action-btn edit-btn page-link-loading"
                                       data-loading-text="Opening editor..."
                                       title="Edit Category">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button type="button"
                                            class="action-btn delete-btn openDeleteModal"
                                            data-action="{{ route('categories.destroy', $category->id) }}"
                                            title="Delete Category">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:30px;" id="found">
                                @if(!empty($search))
                                    <i class="fas fa-search" style="font-size:24px;color:var(--c-empty-icon);display:block;margin-bottom:8px;"></i>
                                    No categories found matching &ldquo;<strong>{{ $search }}</strong>&rdquo;.
                                @else
                                    <i class="fas fa-tags" style="font-size:24px;color:var(--c-empty-icon);display:block;margin-bottom:8px;"></i>
                                    No categories found.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($categories->hasPages())
            <nav aria-label="Page navigation" class="categories-pagination">
                <ul class="pagination-list">

                    {{-- Previous --}}
                    @if ($categories->onFirstPage())
                        <li class="page-btn disabled"><span><i class="fa fa-angle-left"></i></span></li>
                    @else
                        <li class="page-btn">
                            <a href="{{ $categories->previousPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
                                <i class="fa fa-angle-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Page numbers --}}
                    @foreach ($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                        @if ($page == $categories->currentPage())
                            <li class="page-btn active"><span>{{ $page }}</span></li>
                        @else
                            <li class="page-btn">
                                <a href="{{ $url }}" class="page-link-loading" data-loading-text="Loading...">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($categories->hasMorePages())
                        <li class="page-btn">
                            <a href="{{ $categories->nextPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
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

    {{-- Delete Modal --}}
    <x-delete-modal />

    {{-- Bulk Delete Confirm Modal --}}
    <x-delete-multiple-modal
        id="bulkDeleteConfirmModal"
        title="Delete Selected Categories?"
    >
        You are about to delete <strong><span id="bulkDeleteCountText">0</span> categor<span id="bulkDeleteCountWord">y</span></strong>. This action cannot be undone.
    </x-delete-multiple-modal>

    {{-- Hidden form used to submit bulk delete --}}
    <form id="bulkDeleteForm" action="{{ route('categories.bulkDestroy') }}" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
        <div id="bulkDeleteIdsContainer"></div>
    </form>

    <script>
        // Search on Enter (same as products page)
        document.getElementById('searchInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') showLoading('Searching...');
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
                bulkDeleteCountWord.textContent = ids.length === 1 ? 'y' : 'ies';
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
