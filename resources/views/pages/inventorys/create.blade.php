@extends('layouts.master')

@section('pageTitle')
    Add Inventory Item
@endsection

@section('content')
<div class="content-section">
    <h2><i class="fas fa-plus"></i> Add Inventory Item</h2>

    <form action="{{ route('inventory-items.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required>
            @error('name') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="type">Type</label>
            <input type="text" name="type" id="type" value="{{ old('type') }}" placeholder="e.g. raw material, finished good">
            @error('type') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="unit">Unit</label>
            <input type="text" name="unit" id="unit" value="{{ old('unit') }}" placeholder="e.g. pcs, kg, box">
            @error('unit') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price') }}" required>
            @error('price') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="qty">Quantity</label>
            <input type="number" min="0" name="qty" id="qty" value="{{ old('qty') }}" required>
            @error('qty') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="action-btn">Save</button>
            <a href="{{ route('inventory-items.index') }}" class="action-btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
