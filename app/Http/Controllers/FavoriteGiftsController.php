<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteGiftsController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $gifts = [
            ['name' => 'Restless Jar', 'model' => 'Chocolate'],
            ['name' => 'Snow Globe', 'model' => 'Mango Lodge'],
            ['name' => 'Lol Pop', 'model' => 'Tsunami'],
            ['name' => 'Love Potion', 'model' => 'Toxic Waste'],
            ['name' => 'B-Day Candle', 'model' => 'Starry Night'],
            ['name' => 'Lunar Snake', 'model' => 'Lancehead'],
            ['name' => 'Jelly Bunny', 'model' => 'Burnt Cream'],
            ['name' => 'Eternal Candle', 'model' => 'Dreamscape'],
            ['name' => 'Santa Hat', 'model' => 'Blueberry Mint'],
            ['name' => 'Star Notepad', 'model' => 'Twin Stars'],
            ['name' => 'Snow Globe', 'model' => 'Emerald'],
            ['name' => 'Ginger Cookie', 'model' => 'Vanilla'],
            ['name' => 'Desk Calendar', 'model' => 'Lovely'],
            ['name' => 'Spiced Wine', 'model' => 'Aquarium'],
            ['name' => 'Santa Hat', 'model' => 'Berry Frosty'],
            ['name' => 'Homemade Cake', 'model' => 'Pistachio Sky'],
            ['name' => 'Sakura Flower', 'model' => 'Barbia'],
            ['name' => 'Toy Bear', 'model' => 'Red Velvet'],
            ['name' => 'Hypno Lollipop', 'model' => 'Neon Treat'],
            ['name' => 'Tama Gadget', 'model' => 'Peach'],
            ['name' => 'Easter Egg', 'model' => 'Ladybird'],
            ['name' => 'Party Sparkler', 'model' => 'Iridescent'],
            ['name' => 'Eternal Candle', 'model' => 'Warm Embrace'],
            ['name' => 'Candy Cane', 'model' => 'Phoenix'],
            ['name' => 'Bunny Muffin', 'model' => 'Lavender Kiss'],
            ['name' => 'Easter Egg', 'model' => 'Unicorn'],
            ['name' => 'Mad Pumpkin', 'model' => 'The Mocker'],
            ['name' => 'Spy Agaric', 'model' => 'Truffly Trip'],
            ['name' => 'Cookie Heart', 'model' => 'Midsummer'],
            ['name' => 'Hex Pot', 'model' => 'Gummy Worm'],
            ['name' => 'Flying Broom', 'model' => 'Lightspeed'],
            ['name' => 'Jester Hat', 'model' => 'Whimsy Bells'],
            ['name' => 'Witch Hat', 'model' => 'Junk Mage'],
            ['name' => 'Evil Eye', 'model' => 'Apex Predator'],
            ['name' => 'Jack-in-the-Box', 'model' => 'Doughnut'],
            ['name' => 'Ginger Cookie', 'model' => 'Icing Sugar'],
            ['name' => 'Tama Gadget', 'model' => 'Grape'],
            ['name' => 'Big Year', 'model' => 'Van Gogh'],
            ['name' => 'Lol Pop', 'model' => 'Rose Wine'],
            ['name' => 'Lol Pop', 'model' => 'Soap Bubbles'],
            ['name' => 'B-Day Candle', 'model' => 'Melted Butter'],
            ['name' => 'Xmas Stocking', 'model' => 'Electric Sock'],
        ];

        $baseQuery = Gift::where(function ($query) use ($gifts) {
            foreach ($gifts as $gift) {
                $query->orWhere(function ($q) use ($gift) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($gift['name']) . '%'])
                      ->whereRaw('LOWER(model) LIKE ?', ['%' . strtolower($gift['model']) . '%']);
                });
            }
        });

        // Получаем все подарки для подсчета общей суммы
        $allFavoriteGifts = (clone $baseQuery)->with('prices')->get();
        $totalPrice = $allFavoriteGifts->sum(function ($gift) {
            return $gift->prices->last()?->price ?? 0;
        });

        // Получаем отсортированные и пагинированные подарки для отображения
        $favoriteGifts = (clone $baseQuery)
            ->with('prices')
            ->orderBy($sortField, $sortDirection)
            ->paginate(50)
            ->withQueryString();

        return view('favorites.index', compact('favoriteGifts', 'totalPrice', 'sortField', 'sortDirection'));
    }
} 
