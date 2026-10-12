@extends('layouts.master')

@section('pageTitle')
    Edit Inventory Item
@endsection

@section('content')
    <div class="modal-content">
        <a href="{{ route('inventory-items.index') }}" id="backBtn" class="btn btn-back" aria-label="Back to inventory items list">
            <i class="fas fa-chevron-left"></i> Back
        </a>
        <h2><i class="fas fa-edit"></i> Edit Inventory Item</h2>

        <form id="inventoryItemForm" action="{{ route('inventory-items.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label for="name"><i class="fas fa-tag"></i> Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}">
                    @error('name')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="type"><i class="fas fa-layer-group"></i> Type</label>
                    <select name="type" id="type">
                        <option value="">Select type</option>
                        @foreach($types as $type)
                            <option value="{{ $type->name }}" {{ old('type', $item->type) == $type->name ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="unit"><i class="fas fa-balance-scale"></i> Unit</label>
                    <select name="unit" id="unit">
                        <option value="">Select unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->name }}" {{ old('unit', $item->unit) == $unit->name ? 'selected' : '' }}>
                                {{ $unit->name }} @if($unit->abbreviation) ({{ $unit->abbreviation }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('unit')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price"><i class="fas fa-dollar-sign"></i> Price</label>
                    <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price', $item->price) }}">
                    @error('price')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="qty"><i class="fas fa-cubes"></i> Quantity</label>
                    <input type="number" min="0" name="qty" id="qty" value="{{ old('qty', $item->qty) }}">
                    @error('qty')
                    <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn-update">
                    <i class="fas fa-sync-alt"></i> Update Item
                </button>
            </div>
        </form>
    </div>
    <script>
        const overlay     = document.getElementById('loading-overlay');
        const loadingText = document.getElementById('loading-text');
        const form        = document.getElementById('inventoryItemForm');

        document.getElementById('backBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (loadingText) loadingText.textContent = 'Going back...';
            if (overlay) overlay.style.display = 'flex';
            window.location.href = this.getAttribute('href');
        });

        form.addEventListener('submit', function() {
            if (loadingText) loadingText.textContent = 'Updating...';
            if (overlay) overlay.style.display = 'flex';
        });
    </script>
@endsection
