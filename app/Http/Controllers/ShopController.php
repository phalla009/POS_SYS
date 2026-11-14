<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ShopController extends Controller
{
    // បញ្ជី product សម្រាប់អតិថិជន ដាក់ជាក្រុមតាម category (public, មិនបាច់ login)
    public function index()
    {
        $groups = Product::with(['category', 'images'])
            ->where('status', 'active')
            ->latest()
            ->get()
            ->groupBy(fn ($product) => $product->category->name ?? 'Other')
            ->sortKeys();

        return view('shop.index', compact('groups'));
    }

    // បន្ថែម method show សម្រាប់បង្ហាញព័ត៌មានលម្អិតរបស់ Product នីមួយៗ
    public function show($id)
    {
        $product = Product::with(['category', 'images'])
            ->where('status', 'active')
            ->findOrFail($id);

        return view('shop.show', compact('product'));
    }
}
