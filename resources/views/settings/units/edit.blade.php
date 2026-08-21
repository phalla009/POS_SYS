@extends('layouts.master')

@section('pageTitle')
    Edit Unit
@endsection

@section('content')
    <div class="modal-content">
        <a href="{{ route('units.index') }}" id="backBtn" class="btn btn-back" aria-label="Back to units list">
            <i class="fas fa-chevron-left"></i> Back
        </a>
        <h2><i class="fas fa-pen"></i> Edit Unit: {{ $unit->name }}</h2>

        <form id="unitEditForm" action="{{ route('units.update', $unit->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $unit->name) }}"  maxlength="255">
                    @error('name')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-row">
{{--                <div class="form-group">--}}
{{--                    <label for="abbreviation">Abbreviation</label>--}}
{{--                    <input type="text" name="abbreviation" id="abbreviation" value="{{ old('abbreviation', $unit->abbreviation) }}" maxlength="20" placeholder="e.g. kg, pcs, box">--}}
{{--                    @error('abbreviation')--}}
{{--                    <p class="text-danger mt-1">{{ $message }}</p>--}}
{{--                    @enderror--}}
{{--                </div>--}}
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4">{{ old('description', $unit->description) }}</textarea>
                    @error('description')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>


            <div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Unit
                </button>
            </div>
        </form>
    </div>

    <script>
        const overlay     = document.getElementById('loading-overlay');
        const loadingText = document.getElementById('loading-text');
        const form        = document.getElementById('unitEditForm');

        document.getElementById('backBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (loadingText) loadingText.textContent = 'Going back...';
            if (overlay) overlay.style.display = 'flex';
            window.location.href = this.getAttribute('href');
        });

        document.getElementById('cancelBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (loadingText) loadingText.textContent = 'Cancelling...';
            if (overlay) overlay.style.display = 'flex';
            window.location.href = this.getAttribute('href');
        });

        form.addEventListener('submit', function() {
            if (loadingText) loadingText.textContent = 'Updating...';
            if (overlay) overlay.style.display = 'flex';
        });
    </script>
@endsection
