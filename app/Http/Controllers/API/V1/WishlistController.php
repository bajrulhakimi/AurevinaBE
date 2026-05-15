<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle(Request $request)
    {
        $data = [
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ];

        $wishlist = Wishlist::where($data)->first();

        if ($wishlist) {
            $wishlist->delete();
            return $this->successResponse('Produk dihapus dari wishlist');
        }

        Wishlist::create($data);
        return $this->successResponse('Produk ditambahkan ke wishlist', [], 201);
    }
}
