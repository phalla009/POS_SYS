@extends('layouts.master')

@section('pageTitle')
    Unit Details
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
    </style>
@endsection

@section('content')

    {{-- Loading Overlay --}}
    <div id="loading-overlay">
        <div class="spinner"></div>
        <div id="loading-text">Loading...</div>
    </div>

    <div class="modal-content" role="main" aria-label="Unit Details">
        <a href="{{ route('units.index') }}" id="backBtn" class="btn btn-back">
            <i class="fas fa-chevron-left"></i> Back
        </a>

        <h2><i class="fas fa-ruler"></i> Unit Details: {{ $unit->name }}</h2>

        {{-- Row 1: Name & Abbreviation --}}
        <div class="info-row">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-tag"></i> Unit Name</div>
                <div class="info-value">{{ $unit->name }}</div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-compress-alt"></i> Abbreviation</div>
                <div class="info-value">{{ $unit->abbreviation ?? '-' }}</div>
            </div>
        </div>

        {{-- Row 2: Description (Full Width) --}}
        <div class="info-row" style="grid-template-columns: 1fr;">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-align-left"></i> Description</div>
                <div class="info-value">{{ $unit->description ?? '-' }}</div>
            </div>
        </div>

        {{-- Row 3: Created At & Last Updated --}}
        <div class="info-row">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-calendar-plus"></i> Created At</div>
                <div class="info-value">{{ $unit->created_at ? $unit->created_at->timezone('Asia/Phnom_Penh')->format('d M, Y h:i A') : 'N/A' }}</div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-clock"></i> Last Updated</div>
                <div class="info-value">{{ $unit->updated_at ? $unit->updated_at->timezone('Asia/Phnom_Penh')->format('d M, Y h:i A') : 'N/A' }}</div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('units.edit', $unit->id) }}" class="btn btn-primary page-link-loading" data-loading-text="Opening editor...">
                <i class="fas fa-pen"></i> Edit Unit
            </a>
        </div>
    </div>

    <script>
        const overlay     = document.getElementById('loading-overlay');
        const loadingText = document.getElementById('loading-text');

        document.getElementById('backBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (loadingText) loadingText.textContent = 'Going back...';
            if (overlay) overlay.style.display = 'flex';
            window.location.href = this.getAttribute('href');
        });

        document.querySelectorAll('.page-link-loading').forEach(function(link) {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                const msg  = this.getAttribute('data-loading-text') || 'Loading...';
                if (href && href !== '#' && href !== 'javascript:void(0)') {
                    e.preventDefault();
                    if (loadingText) loadingText.textContent = msg;
                    if (overlay) overlay.style.display = 'flex';
                    window.location.href = href;
                }
            });
        });
    </script>

@endsection
