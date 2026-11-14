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
        /* Controls row (same layout as products page) */
        .controls-header {
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .controls-header .btn {
            flex-shrink: 0;
        }

        /* Abbreviation Badge */
        .abbr-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #f1f5f9;
            color: #475569;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

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

            .controls-header .btn-primary {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .controls-header form,
            .controls-header .form-group {
                width: 100%;
                min-width: 100% !important;
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
            <div class="filter-controls">
                <a href="{{ route('units.create') }}"
                   class="btn btn-primary page-link-loading"
                   data-loading-text="Loading add..."
                   style="white-space:nowrap;">
                    <i class="fas fa-circle-plus"></i> Add New Unit
                </a>
            </div>

            {{-- Search (same style as products page, press Enter to search) --}}
            <form method="GET" action="{{ route('units.index') }}" id="searchForm" style="margin:0;">
                <div class="form-group" style="position:relative; min-width:800px; margin:0;">
                    <i class="fas fa-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#b39b85;font-size:14px;pointer-events:none;"></i>
                    <input type="text" name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Search Units..."
                           id="searchInput"
                           autocomplete="off"
                           style="border-radius:24px; padding-left:38px;">
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-container" role="region" aria-label="Units table">
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    {{--                    <th>Abbreviation</th>--}}
                    <th>Description</th>
                    <th>Updated Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($units as $unit)
                    <tr>
                        <td data-label="No">#{{ $loop->iteration + ($units->currentPage() - 1) * $units->perPage() }}</td>
                        <td data-label="Name" style="color: black; font-weight: 600;">{{ $unit->name }}</td>
                        {{--                        <td data-label="Abbreviation">--}}
                        {{--                            @if(!empty($unit->abbreviation))--}}
                        {{--                                <span class="abbr-badge">{{ $unit->abbreviation }}</span>--}}
                        {{--                            @else--}}
                        {{--                                <span style="color: #9ca3af;">N/A</span>--}}
                        {{--                            @endif--}}
                        {{--                        </td>--}}
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
        // ---- Page Loading Spinner Helper ----
        function showLoading(msg) {
            const ov = document.getElementById('loading-overlay');
            const lt = document.getElementById('loading-text');
            if (!ov) return;
            if (lt) lt.textContent = msg || 'Loading...';
            ov.style.display = 'flex';
        }

        // Search on Enter (same as products page)
        document.getElementById('searchInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') showLoading('Searching...');
        });

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
