<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::with(['user', 'product', 'repliedBy'])
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return $this->successResponse('Daftar ulasan berhasil diambil', ReviewResource::collection($reviews));
    }

    public function reply(Request $request, Review $review)
    {
        $data = $request->validate([
            'admin_reply' => 'required|string|max:2000',
        ]);

        $review->update([
            'admin_reply' => $data['admin_reply'],
            'replied_at' => now(),
            'replied_by' => $request->user()->id,
        ]);

        return $this->successResponse('Balasan ulasan berhasil disimpan', new ReviewResource($review->load(['user', 'product', 'repliedBy'])));
    }
}
