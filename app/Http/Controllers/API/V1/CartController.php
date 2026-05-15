<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Services\CartService;
use Illuminate\Http\Request;
use App\Models\CartItem;

class CartController extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

    public function index(Request $request)
    {
        $items = $this->cartService->getCart($request->user()->id);
        return $this->successResponse('Keranjang berhasil diambil', CartItemResource::collection($items));
    }

    public function add(StoreCartItemRequest $request)
    {
        $this->cartService->addToCart($request->user()->id, $request->validated());
        $items = $this->cartService->getCart($request->user()->id);

        return $this->successResponse('Produk ditambahkan ke keranjang', CartItemResource::collection($items), 201);
    }

    public function update(UpdateCartItemRequest $request, $id)
    {
        $item = CartItem::where('id', $id)
            ->whereHas('cart', fn($query) => $query->where('user_id', $request->user()->id))
            ->firstOrFail();

        $item->update($request->validated());

        return $this->successResponse('Item keranjang diperbarui', new CartItemResource($item));
    }

    public function remove(Request $request, $id)
    {
        $item = CartItem::where('id', $id)
            ->whereHas('cart', fn($query) => $query->where('user_id', $request->user()->id))
            ->firstOrFail();

        $item->delete();

        return $this->successResponse('Item keranjang dihapus');
    }
}
