<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function index()
    {
        $totalTypes = Type::count();
        $types = Type::orderBy('name')->paginate(10)->withQueryString();

        return view('settings/types.index', compact('types', 'totalTypes'));
    }

    public function create()
    {
        return view('settings/types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:types,name',
            'description' => 'nullable|string',
        ]);

        Type::create($validated);

        return redirect()->route('types.index')->with('success', 'Type added successfully.');
    }

    public function show(string $id)
    {
        $type = Type::findOrFail($id);
        return view('settings/types.show', compact('type'));
    }

    public function edit(string $id)
    {
        $type = Type::findOrFail($id);
        return view('settings/types.edit', compact('type'));
    }

    public function update(Request $request, string $id)
    {
        $type = Type::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:types,name,' . $type->id,
            'description' => 'nullable|string',
        ]);

        $type->update($validated);

        return redirect()->route('types.index')->with('success', 'Type updated successfully.');
    }

    public function destroy(string $id)
    {
        $type = Type::findOrFail($id);
        $type->delete();

        return redirect()->route('types.index')->with('success', 'Type deleted successfully.');
    }
}
