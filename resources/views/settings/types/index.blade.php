@extends('layouts.master')

@section('pageTitle')
    Types
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/delete_form.css') }}">
    <script src="{{ URL::asset('js/form.js') }}"></script>
    <script src="{{ URL::asset('js/delete_form.js') }}"></script>
    <style>
        /* Pagination */
        .inventory-pagination {
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

    <div class="content-section" id="types">
        <h2><i class="fas fa-layer-group"></i> Types</h2>

        <div style="margin-bottom:12px;">
            <a href="{{ route('types.create') }}" class="btn btn-primary page-link-loading" data-loading-text="Loading add...">
                <i class="fas fa-circle-plus"></i> Add New Type
            </a>
        </div>

        {{-- Stats Grid --}}
        {{--        <div class="stats-grid">--}}
        {{--            <div class="stat-card">--}}
        {{--                <h3>{{ $totalTypes }}</h3>--}}
        {{--                <p>Total Types</p>--}}
        {{--            </div>--}}
        {{--        </div>--}}

        {{-- Table --}}
        <div class="table-container" role="region" aria-label="Types table">
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
                @forelse ($types as $type)
                    <tr>
                        <td data-label="No">#{{ $loop->iteration + ($types->currentPage() - 1) * $types->perPage() }}</td>
                        <td data-label="Name">{{ $type->name }}</td>
                        <td data-label="Description">{{ $type->description ?? 'N/A' }}</td>
                        <td data-label="Updated Date">{{ $type->updated_at->timezone('Asia/Phnom_Penh')->format('d M, Y h:i A') }}</td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <a href="{{ route('types.show', $type->id) }}"
                                   class="action-btn show-btn page-link-loading"
                                   data-loading-text="Loading details..."
                                   title="View Details">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('types.edit', $type->id) }}"
                                   class="action-btn edit-btn page-link-loading"
                                   data-loading-text="Opening editor..."
                                   title="Edit Type">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button"
                                        class="action-btn delete-btn openDeleteModal"
                                        data-action="{{ route('types.destroy', $type->id) }}"
                                        title="Delete Type">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;" id="found">No types found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($types->hasPages())
            <nav aria-label="Page navigation" class="inventory-pagination">
                <ul class="pagination-list">

                    {{-- Previous --}}
                    @if ($types->onFirstPage())
                        <li class="page-btn disabled"><span> <i class="fa fa-angle-left"></i></span></li>
                    @else
                        <li class="page-btn">
                            <a href="{{ $types->previousPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
                                <i class="fa fa-angle-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Page numbers --}}
                    @foreach ($types->getUrlRange(1, $types->lastPage()) as $page => $url)
                        @if ($page == $types->currentPage())
                            <li class="page-btn active"><span>{{ $page }}</span></li>
                        @else
                            <li class="page-btn">
                                <a href="{{ $url }}" class="page-link-loading" data-loading-text="Loading...">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($types->hasMorePages())
                        <li class="page-btn">
                            <a href="{{ $types->nextPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
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
