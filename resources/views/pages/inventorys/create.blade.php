@extends('layouts.master')

@section('pageTitle')
    Add Inventory Item
@endsection

@section('content')
    <div class="modal-content">
        <a href="{{ route('inventory-items.index') }}" id="backBtn" class="btn btn-back" aria-label="Back to inventory items list">
            <i class="fas fa-chevron-left"></i> Back
        </a>
        <h2><i class="fas fa-boxes"></i> Add Inventory Item</h2>

        <form id="inventoryItemForm" action="{{ route('inventory-items.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" >
                    @error('name')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="type">Type</label>
                    <input type="text" name="type" id="type" value="{{ old('type') }}" placeholder="e.g. raw material, finished good">
                    @error('type')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="unit">Unit</label>
                    <input type="text" name="unit" id="unit" value="{{ old('unit') }}" placeholder="e.g. pcs, kg, box">
                    @error('unit')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price') }}" >
                    @error('price')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="qty">Quantity</label>
                    <input type="number" min="0" name="qty" id="qty" value="{{ old('qty') }}" >
                    @error('qty')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

{{--            <div class="form-row">--}}
{{--                <div class="form-group">--}}
{{--                    <label for="status">Status</label>--}}
{{--                    <select name="status" id="status">--}}
{{--                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>--}}
{{--                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>--}}
{{--                    </select>--}}
{{--                    @error('status')--}}
{{--                    <p class="text-danger mt-1">{{ $message }}</p>--}}
{{--                    @enderror--}}
{{--                </div>--}}
{{--            </div>--}}

            <div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Add Item
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
        const form         = document.getElementById('inventoryItemForm');

        document.getElementById('backBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (loadingText) loadingText.textContent = 'Going back...';
            if (overlay) overlay.style.display = 'flex';
            window.location.href = this.getAttribute('href');
        });

        form.addEventListener('submit', function() {
            loadingText.textContent = 'Saving...';
            overlay.style.display = 'flex';
        });

        document.getElementById('cancelBtn').addEventListener('click', function() {
            form.reset();
        });
    </script>

@endsection
