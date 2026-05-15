<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\OrderItem;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request)
    {
        $orderItemId = $request->order_item_id;

        if ($orderItemId) {
            $belongsToCustomer = OrderItem::where('id', $orderItemId)
                ->where('product_id', $request->product_id)
                ->whereHas('order', fn($query) => $query->where('user_id', $request->user()->id))
                ->exists();

            if (!$belongsToCustomer) {
                return $this->errorResponse('Item pesanan tidak sesuai dengan akun pelanggan.', [], 422);
            }
        }

        $alreadyReviewed = Review::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->when($orderItemId, fn($query) => $query->where('order_item_id', $orderItemId))
            ->exists();

        if ($alreadyReviewed) {
            return $this->errorResponse('Produk ini sudah pernah diberi ulasan.', [], 422);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'order_item_id' => $orderItemId,
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return $this->successResponse('Ulasan berhasil disimpan', new ReviewResource($review->load(['user', 'repliedBy'])), 201);
    }
}
