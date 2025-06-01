<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    /**
     * Display a listing of the gifts.
     */
    public function index(Request $request)
    {
        $query = Gift::query();

        // Поиск по названию
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Сортировка по цене
        if ($request->has('price')) {
            $query->orderBy('price', $request->price);
        }

        $gifts = $query->paginate(12);

        return view('gifts.index', compact('gifts'));
    }

    /**
     * Display the specified gift.
     */
    public function show(Gift $gift)
    {
        return view('gifts.show', compact('gift'));
    }
} 