@extends('layouts.master')

@section('pageTitle')
    Units
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/delete_form.css') }}">
    <script src="{{ URL::asset('js/form.js') }}"></script>
    <script src="{{ URL::asset('js/delete_form.js') }}"></script>
    <style>
        .controls-header {
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Search Bar Styles */
        .search-wrapper {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 1;
            min-width: 250px;
        }
        .search-input-group {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .search-input-group .search-icon {
            position: absolute;
            left: 12px;
            color: #aaa;
            font-size: 13px;
            pointer-events: none;
            z-index: 1;
        }
        .search-input-group input[type="text"] {
            padding: 10px 36px 10px 34px;
            border: 1.5px solid #dde3ec;
            border-radius: 24px;
            font-size: 14px;
            color: #333;
            background: #f9fafc;
            width: 100%;
            max-width: 600px;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
        }
        .search-input-group input[type="text"]:focus {
            border-color: #3498db;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.13);
        }
        .search-input-group input[type="text"]::placeholder { color: #bbb; }
        .search-clear-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: #bbb;
            font-size: 13px;
            padding: 0;
            display: flex;
            align-items: center;
            transition: color 0.15s;
        }
        .search-clear-btn:hover { color: #e74c3c; }
        .search-results-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #eaf4fd;
            color: #2980b9;
            border: 1px solid #b6d9f5;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12.5px;
            font-weight: 500;
            white-space: nowrap;
        }
        .search-results-badge i { font-size: 11px; }

        /* Pagination */
        .inventory-pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .controls-header {
                flex-direction: column;
                align-items: stretch;
            }
            .search-wrapper {
                width: 100%;
            }
            .search-input-group input[type="text"] {
                max-width: 100%;
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

    <div class="content-section" id="units">
        <h2><i class="fas fa-ruler"></i> Units</h2>

        {{-- Controls Header --}}
        <div class="controls-header">
            <a href="{{ route('units.create') }}" class="btn btn-primary page-link-loading" data-loading-text="Loading add...">
                <i class="fas fa-circle-plus"></i> Add New Unit
            </a>

            {{-- Auto Search Form --}}
            <form method="GET" action="{{ route('units.index') }}" class="search-wrapper" id="searchForm">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" id="searchInput"
                           value="{{ $search ?? '' }}"
                           placeholder="Search units..." autocomplete="off">
                    @if(!empty($search))
                        <button type="button" class="search-clear-btn" id="clearSearchBtn" title="Clear">
                            <i class="fas fa-times"></i>
                        </button>
                    @endif
                </div>
            </form>

            {{-- Results Badge --}}
            @if(!empty($search))
                <span class="search-results-badge">
                    <i class="fas fa-filter"></i>
                    {{ $units->total() }} result{{ $units->total() !== 1 ? 's' : '' }} for &ldquo;{{ $search }}&rdquo;
                </span>
            @endif
        </div>

        {{-- Table --}}
        <div class="table-container" role="region" aria-label="Units table">
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Updated Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($units as $unit)
                    <tr>
                        <td data-label="No">#{{ $loop->iteration + ($units->currentPage() - 1) * $units->perPage() }}</td>
                        <td data-label="Name">{{ $unit->name }}</td>
                        <td data-label="Description">{{ $unit->description ?? 'N/A' }}</td>
                        <td data-label="Updated Date">{{ $unit->updated_at->timezone('Asia/Phnom_Penh')->format('d M, Y h:i A') }}</td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <a href="{{ route('units.show', $unit->id) }}"
                                   class="action-btn show-btn page-link-loading"
                                   data-loading-text="Loading details..."
                                   title="View Details">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('units.edit', $unit->id) }}"
                                   class="action-btn edit-btn page-link-loading"
                                   data-loading-text="Opening editor..."
                                   title="Edit unit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button"
                                        class="action-btn delete-btn openDeleteModal"
                                        data-action="{{ route('units.destroy', $unit->id) }}"
                                        title="Delete unit">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:30px;" id="found">
                            @if(!empty($search))
                                <i class="fas fa-search" style="font-size:24px;color:#ccc;display:block;margin-bottom:8px;"></i>
                                No units found matching &ldquo;<strong>{{ $search }}</strong>&rdquo;.
                            @else
                                <i class="fas fa-ruler" style="font-size:24px;color:#ccc;display:block;margin-bottom:8px;"></i>
                                No units found.
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($units->hasPages())
            <nav aria-label="Page navigation" class="inventory-pagination">
                <ul class="pagination-list">

                    {{-- Previous --}}
                    @if ($units->onFirstPage())
                        <li class="page-btn disabled"><span> <i class="fa fa-angle-left"></i></span></li>
                    @else
                        <li class="page-btn">
                            <a href="{{ $units->previousPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
                                <i class="fa fa-angle-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Page numbers --}}
                    @foreach ($units->getUrlRange(1, $units->lastPage()) as $page => $url)
                        @if ($page == $units->currentPage())
                            <li class="page-btn active"><span>{{ $page }}</span></li>
                        @else
                            <li class="page-btn">
                                <a href="{{ $url }}" class="page-link-loading" data-loading-text="Loading...">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($units->hasMorePages())
                        <li class="page-btn">
                            <a href="{{ $units->nextPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-btn disabled"><span> <i class="fa fa-angle-right"></i></span></li>
                    @endif

                </ul>
            </nav>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-delete-modal />

    <script>
        // ---- Live Auto Search (Debounced 400ms) ----
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        let searchTimer = null;

        if (searchInput && searchForm) {
            // Restore focus and cursor position after re-render
            if (searchInput.value) {
                searchInput.focus();
                const valLen = searchInput.value.length;
                searchInput.setSelectionRange(valLen, valLen);
            }

            // Submit search automatically 400ms after user finishes typing
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    searchForm.submit();
                }, 400);
            });
        }

        // ---- Clear Search Button ----
        const clearBtn = document.getElementById('clearSearchBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                window.location.href = "{{ route('units.index') }}";
            });
        }

        // ---- Page Loading Spinner Helper ----
        function showLoading(msg) {
            const ov = document.getElementById('loading-overlay');
            const lt = document.getElementById('loading-text');
            if (!ov) return;
            if (lt) lt.textContent = msg || 'Loading...';
            ov.style.display = 'flex';
        }

        document.querySelectorAll('.page-link-loading').forEach(function(link) {
            link.addEventListener('click', function(e) {
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
