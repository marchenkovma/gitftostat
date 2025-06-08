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

        // Если применены фильтры, сортируем по цене
        if ($request->filled('name') || $request->filled('model')) {
            $query->with(['prices' => function($q) {
                $q->latest();
            }])
            ->orderBy(function($query) {
                $query->select('price')
                    ->from('gift_prices')
                    ->whereColumn('gift_id', 'gifts.id')
                    ->latest()
                    ->limit(1);
            }, 'asc')
            ->orderBy('name', 'asc');
        } else {
            // Если фильтры не применены, сортируем только по имени
            $query->orderBy('name', 'asc');
        }

        $gifts = $query->paginate(50)->withQueryString();

        // Получаем уникальные имена
        $names = Gift::distinct()->pluck('name')->sort()->values();

        // Получаем модели, сгруппированные по именам
        $modelsByName = Gift::select('name', 'model')
            ->distinct()
            ->get()
            ->groupBy('name')
            ->map(function ($items) {
                return $items->pluck('model')->sort()->values();
            });

        return view('gifts.index', compact('gifts', 'names', 'modelsByName'));
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