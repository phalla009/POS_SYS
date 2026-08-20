@extends('layouts.master')

@section('pageTitle')
    Inventory Item Details
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <script src="{{ URL::asset('js/form.js') }}"></script>
    <style>
        #loading-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.85);
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 99999;
        }
        .spinner {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 60px; height: 60px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        #loading-text { margin-top: 15px; font-size: 16px; color: #333; }

        .info-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }

        .info-row-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }

        .info-box {
            padding: 14px 18px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .info-box:hover {
            border-color: #a5b4fc;
            box-shadow: 0 4px 12px rgba(99,102,241,0.08);
        }

        .info-label {
            font-size: 11px;
            font-weight: 700;
            color: #4338ca;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-label i {
            font-size: 11px;
            color: #6366f1;
        }

        .info-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
        }

        .stock-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 600;
        }
        .stock-in  { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .stock-low { background: #fef9c3; color: #a16207; border: 1px solid #fde047; }
        .stock-out { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .stock-badge i { font-size: 8px; color: inherit; }
    </style>
@endsection

@section('content')

    {{-- Loading Overlay --}}
    <div id="loading-overlay">
        <div class="spinner"></div>
        <div id="loading-text">Going back...</div>
    </div>

    <div class="modal-content" role="main" aria-label="Inventory Item Details">
        <a href="{{ route('inventory-items.index') }}" id="backBtn" class="btn btn-back">
            <i class="fas fa-chevron-left"></i> Back
        </a>

        <h2><i class="fas fa-boxes"></i> Inventory Item Details</h2>

        {{-- Row 1: Name & Type --}}
        <div class="info-row">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-tag"></i> Item Name</div>
                <div class="info-value">{{ $item->name }}</div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-layer-group"></i> Type</div>
                <div class="info-value">{{ $item->type ?? '-' }}</div>
            </div>
        </div>

        {{-- Row 2: Unit, Price, Qty --}}
        <div class="info-row-3">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-ruler"></i> Unit</div>
                <div class="info-value">{{ $item->unit ?? '-' }}</div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-dollar-sign"></i> Price</div>
                <div class="info-value">${{ number_format($item->price, 2) }}</div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-cubes"></i> Quantity</div>
                <div class="info-value">{{ $item->qty }}</div>
            </div>
        </div>

        {{-- Row 3: Status --}}
        <div class="info-row">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-circle-check"></i> Status</div>
                <div class="info-value">
                    @if($item->status === 'out')
                        <span class="stock-badge stock-out"><i class="fas fa-circle"></i> Out of Stock</span>
                    @elseif($item->status === 'low')
                        <span class="stock-badge stock-low"><i class="fas fa-circle"></i> Low Stock</span>
                    @else
                        <span class="stock-badge stock-in"><i class="fas fa-circle"></i> In Stock</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Row 4: Created At & Last Updated --}}
        <div class="info-row">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-calendar-plus"></i> Created At</div>
                <div class="info-value">{{ $item->created_at ? $item->created_at->timezone('Asia/Phnom_Penh')->format('d M, Y h:i A') : 'N/A' }}</div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-clock"></i> Last Updated</div>
                <div class="info-value">{{ $item->updated_at ? $item->updated_at->timezone('Asia/Phnom_Penh')->format('d M, Y h:i A') : 'N/A' }}</div>
            </div>
        </div>

    </div>

    <script>
        document.getElementById('backBtn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('loading-overlay').style.display = 'flex';
            window.location.href = this.getAttribute('href');
        });
    </script>

@endsection
