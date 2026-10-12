@extends('layouts.master')

@section('pageTitle')
    Add Type
@endsection

@section('headerBlock')
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <script src="{{ URL::asset('js/form.js') }}"></script>
    <style>
        #loading-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.85);
            display: flex;
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
        .text-danger { color: red; margin-top: 4px; }
    </style>
@endsection

@section('content')

    {{-- Loading Overlay --}}
    <div id="loading-overlay">
        <div class="spinner"></div>
        <div id="loading-text">Loading...</div>
    </div>

    @if(session('success'))
        <div id="successMessage" class="custom-success">
            <i class="fas fa-check-circle" style="color: green; margin-right: 8px;"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="modal-content" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
        <a href="{{ route('types.index') }}" id="backBtn" class="btn btn-back" aria-label="Back to types list">
            <i class="fas fa-chevron-left"></i> Back
        </a>

        <h2><i class="fas fa-layer-group"></i> Add New Type</h2>

        <form id="typeForm" action="{{ route('types.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name"><i class="fas fa-tag"></i> Name:</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Enter type name" maxlength="255">
                    @error('name')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description:</label>
                    <textarea name="description" id="description" rows="6" placeholder="Enter description" style="height: 120px; resize: none;">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Save Type
                </button>
                <button id="cancelBtn" type="button" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>

    <script>
        const overlay     = document.getElementById('loading-overlay');
        const loadingText = document.getElementById('loading-text');
        const form        = document.getElementById('typeForm');

        window.addEventListener('load', function () {
            if (overlay) overlay.style.display = 'none';
        });

        document.getElementById('backBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (loadingText) loadingText.textContent = 'Going back...';
            if (overlay) overlay.style.display = 'flex';
            window.location.href = this.getAttribute('href');
        });

        if (form) {
            form.addEventListener('submit', function() {
                if (loadingText) loadingText.textContent = 'Saving...';
                if (overlay) overlay.style.display = 'flex';
            });
        }

        const cancelBtn = document.getElementById('cancelBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                if (form) form.reset();
            });
        }
    </script>
@endsection
