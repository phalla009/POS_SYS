@extends('layouts.master')

@section('pageTitle')
    Add Unit
@endsection

@section('content')
    <div class="modal-content">
        <a href="{{ route('units.index') }}" id="backBtn" class="btn btn-back" aria-label="Back to units list">
            <i class="fas fa-chevron-left"></i> Back
        </a>
        <h2><i class="fas fa-ruler"></i> Add New Unit</h2>

        <form id="unitForm" action="{{ route('units.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Name </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Enter unit name" maxlength="255">
                    @error('name')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-row">
{{--                <div class="form-group">--}}
{{--                    <label for="abbreviation">Abbreviation</label>--}}
{{--                    <input type="text" name="abbreviation" id="abbreviation" value="{{ old('abbreviation') }}" maxlength="20" placeholder="e.g. kg, pcs, box">--}}
{{--                    @error('abbreviation')--}}
{{--                    <p style="color: #ff0000; font-size: 13px; margin-top: 5px; margin-bottom: 0;">{{ $message }}</p>--}}
{{--                    @enderror--}}
{{--                </div>--}}
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4" placeholder="Enter description">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Save Unit
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
        const form        = document.getElementById('unitForm');

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
