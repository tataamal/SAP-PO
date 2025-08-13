<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionMapping;

class MenuController extends Controller
{
    public function index()
    {
        // Ambil hanya plant unik
        $plants = ProductionMapping::select('plant')
                    ->groupBy('plant')
                    ->orderBy('plant')
                    ->get();

        return view('menu', compact('plants'));
    }

    public function show($plant)
    {
        $data = ProductionMapping::where('plant', $plant)->get();
        return view('menu', compact('data', 'plant'));
    }
}