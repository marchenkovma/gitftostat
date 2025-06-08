<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    public function index()
    {
        $gifts = Gift::orderBy('name', 'asc')
            ->paginate(50);

        return view('gifts.index', compact('gifts'));
    }
} 