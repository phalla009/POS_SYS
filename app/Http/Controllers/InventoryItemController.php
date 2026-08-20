<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allItems = InventoryItem::all();

        $totalItems      = $allItems->sum('qty');
        $lowStockItems   = $allItems->filter(function ($item) {
            return $item->qty >= 1 && $item->qty <= InventoryItem::LOW_STOCK_THRESHOLD;
        })->count();
        $outOfStockItems = $allItems->where('qty', '<=', 0)->count();
        $inventoryValue  = $allItems->sum(function ($item) {
            return $item->qty * $item->price;
        });

        $items = InventoryItem::orderBy('name')->paginate(10)->withQueryString();

        return view('pages.inventorys.index', compact(
            'items',
            'totalItems',
            'lowStockItems',
            'outOfStockItems',
            'inventoryValue'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.inventorys.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'qty'   => 'required|integer|min:0',
            'unit'  => 'nullable|string|max:50',
            'type'  => 'nullable|string|max:50',
        ]);

        InventoryItem::create($validated);

        return redirect()
            ->route('inventory-items.index')
            ->with('success', 'Item "' . $validated['name'] . '" added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = InventoryItem::findOrFail($id);

        return view('pages.inventorys.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = InventoryItem::findOrFail($id);

        return view('pages.inventorys.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'qty'   => 'required|integer|min:0',
            'unit'  => 'nullable|string|max:50',
            'type'  => 'nullable|string|max:50',
        ]);

        $item->update($validated);

        return redirect()
            ->route('inventory-items.index')
            ->with('success', 'Item "' . $item->name . '" updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = InventoryItem::findOrFail($id);
        $name = $item->name;
        $item->delete();

        return redirect()
            ->route('inventory-items.index')
            ->with('success', 'Item "' . $name . '" deleted successfully.');
    }

    /**
     * Remove multiple resources from storage at once.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:inventory_items,id',
        ]);

        InventoryItem::whereIn('id', $validated['ids'])->delete();

        return redirect()
            ->route('inventory-items.index')
            ->with('success', 'Selected items deleted successfully.');
    }
}
