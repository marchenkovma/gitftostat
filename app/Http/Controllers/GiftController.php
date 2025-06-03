<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    public function index()
    {
        $gifts = Gift::orderBy('name', 'asc')
            ->paginate(20);

        return view('gifts.index', compact('gifts'));
    }

    public function show(Gift $gift)
    {
        $gift->load('prices');
        return view('gifts.show', compact('gift'));
    }
} 