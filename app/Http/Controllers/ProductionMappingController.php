<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionMapping;

class ProductionMappingController extends Controller
{
   public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'categories' => 'required|string|max:255',
            'bagian' => 'nullable|string',
            'plant' => 'required|string|max:255',
        ]);

        \App\Models\ProductionMapping::create($validated);

        return redirect()->route('menu')->with('success', 'Data berhasil ditambahkan.');
    }
}
