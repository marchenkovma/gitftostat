<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    public function index(Request $request)
    {
        $query = Gift::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('model')) {
            $query->where('model', 'like', '%' . $request->model . '%');
        }

        $gifts = $query->orderBy('name', 'asc')
            ->paginate(50)
            ->withQueryString();

        // Получаем списки для фильтров
        $names = Gift::distinct()->pluck('name')->sort()->values();
        $models = Gift::distinct()->pluck('model')->sort()->values();

        return view('gifts.index', compact('gifts', 'names', 'models'));
    }

    public function getFilterOptions(Request $request)
    {
        $names = Gift::distinct()->pluck('name')->sort()->values();
        $models = Gift::distinct()->pluck('model')->sort()->values();

        return response()->json([
            'names' => $names,
            'models' => $models
        ]);
    }
} 