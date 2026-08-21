<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $totalUnits = Unit::count();

        $units = Unit::orderBy('name')->paginate(10)->withQueryString();

        return view('settings/units.index', compact('units', 'totalUnits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings/units.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255|unique:units,name',
            'abbreviation' => 'nullable|string|max:20',
            'description'  => 'nullable|string',
        ]);

        Unit::create($validated);

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit "' . $validated['name'] . '" added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unit = Unit::findOrFail($id);

        return view('settings/units.show', compact('unit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $unit = Unit::findOrFail($id);

        return view('settings/units.edit', compact('unit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string|max:255|unique:units,name,' . $unit->id,
            'abbreviation' => 'nullable|string|max:20',
            'description'  => 'nullable|string',
        ]);

        $unit->update($validated);

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit "' . $unit->name . '" updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);
        $name = $unit->name;
        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit "' . $name . '" deleted successfully.');
    }

    /**
     * Remove multiple resources from storage at once.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:units,id',
        ]);

        Unit::whereIn('id', $validated['ids'])->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'Selected units deleted successfully.');
    }
}
