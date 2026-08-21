@extends('layouts.master')

@section('pageTitle')
    Edit Type
@endsection

@section('content')
    <div class="modal-content">
        <a href="{{ route('types.index') }}" id="backBtn" class="btn btn-back" aria-label="Back to types list">
            <i class="fas fa-chevron-left"></i> Back
        </a>
        <h2><i class="fas fa-pen"></i> Edit Type: {{ $type->name }}</h2>

        <form id="typeForm" action="{{ route('types.update', $type->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $type->name) }}" placeholder="Enter type name" maxlength="255">
                    @error('name')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4" placeholder="Enter description">{{ old('description', $type->description) }}</textarea>
                    @error('description')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Type
                </button>
            </div>
        </form>
    </div>

    <script>
        const overlay     = document.getElementById('loading-overlay');
        const loadingText = document.getElementById('loading-text');
        const form        = document.getElementById('typeForm');

        document.getElementById('backBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (loadingText) loadingText.textContent = 'Going back...';
            if (overlay) overlay.style.display = 'flex';
            window.location.href = this.getAttribute('href');
        });

        form.addEventListener('submit', function() {
            if (loadingText) loadingText.textContent = 'Saving...';
            if (overlay) overlay.style.display = 'flex';
        });

        document.getElementById('cancelBtn').addEventListener('click', function() {
            form.reset();
        });
    </script>
@endsection
