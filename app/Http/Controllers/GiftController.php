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
            $query->where('name', $request->name);
        }

        if ($request->filled('model')) {
            $query->where('model', 'like', '%' . $request->model . '%');
        }

        // Если применены фильтры, сортируем по цене
        if ($request->filled('name') || $request->filled('model')) {
            $query->join('gift_prices', function($join) {
                $join->on('gifts.id', '=', 'gift_prices.gift_id')
                    ->whereRaw('gift_prices.id = (SELECT id FROM gift_prices WHERE gift_id = gifts.id ORDER BY created_at DESC LIMIT 1)');
            })
            ->orderBy('gift_prices.price', 'asc')
            ->orderBy('gifts.name', 'asc')
            ->select('gifts.*');
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