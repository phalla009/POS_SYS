@extends('layouts.master')

@section('pageTitle')
    Inventory Items
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/delete_form.css') }}">
    <script src="{{ URL::asset('js/form.js') }}"></script>
    <script src="{{ URL::asset('js/delete_form.js') }}"></script>
    <style>
        .stock-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            width: fit-content;
        }
        .stock-in  { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .stock-low { background: #fef9c3; color: #a16207; border: 1px solid #fde047; }
        .stock-out { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

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

    <div class="content-section" id="inventory-items">
        <h2><i class="fas fa-boxes"></i> Inventory Items</h2>

        <div style="margin-bottom:12px;">
            <a href="{{ route('inventory-items.create') }}" class="btn btn-primary page-link-loading" data-loading-text="Loading add...">
                <i class="fas fa-circle-plus"></i> Add New Items
            </a>
        </div>

        {{-- Stats Grid --}}
        <div class="stats-grid">
            <div class="stat-card">
                <h3>{{ $totalItems }}</h3>
                <p>Total Stocks</p>
            </div>
            <div class="stat-card">
                <h3>{{ $lowStockItems }}</h3>
                <p>Low Stock Products</p>
            </div>
            <div class="stat-card">
                <h3>{{ $outOfStockItems }}</h3>
                <p>Out of Stocks</p>
            </div>
            <div class="stat-card">
                <h3>${{ number_format($inventoryValue, 2) }}</h3>
                <p>Inventory Value</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-container" role="region" aria-label="Inventory items table">
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Status</th>
                    <th>Updated Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td data-label="No">#{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                        <td data-label="Name">{{ $item->name }}</td>
                        <td data-label="Type">{{ $item->type ?? '-' }}</td>
                        <td data-label="Unit">{{ $item->unit ?? '-' }}</td>
                        <td data-label="Price">${{ number_format($item->price, 2) }}</td>
                        <td data-label="Qty">{{ $item->qty }}</td>
                        <td data-label="Status">
                            @if($item->status === 'out')
                                <span class="stock-badge stock-out">Out of Stock</span>
                            @elseif($item->status === 'low')
                                <span class="stock-badge stock-low">Low Stock</span>
                            @else
                                <span class="stock-badge stock-in">In Stock</span>
                            @endif
                        </td>
                        <td data-label="Updated Date">{{ $item->updated_at->timezone('Asia/Phnom_Penh')->format('d M, Y h:i A') }}</td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <a href="{{ route('inventory-items.edit', $item->id) }}"
                                   class="action-btn edit-btn page-link-loading"
                                   data-loading-text="Opening editor..."
                                   title="Edit item">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button"
                                        class="action-btn delete-btn openDeleteModal"
                                        data-action="{{ route('inventory-items.destroy', $item->id) }}"
                                        title="Delete item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;" id="found">No items found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($items->hasPages())
            <nav aria-label="Page navigation" class="inventory-pagination">
                <ul class="pagination-list">

                    {{-- Previous --}}
                    @if ($items->onFirstPage())
                        <li class="page-btn disabled"><span> <i class="fa fa-angle-left"></i></span></li>
                    @else
                        <li class="page-btn">
                            <a href="{{ $items->previousPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
                                <i class="fa fa-angle-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Page numbers --}}
                    @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                        @if ($page == $items->currentPage())
                            <li class="page-btn active"><span>{{ $page }}</span></li>
                        @else
                            <li class="page-btn">
                                <a href="{{ $url }}" class="page-link-loading" data-loading-text="Loading...">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($items->hasMorePages())
                        <li class="page-btn">
                            <a href="{{ $items->nextPageUrl() }}" class="page-link-loading" data-loading-text="Loading...">
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
