<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::where('status', 'published')
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse('Artikel blog berhasil diambil', BlogResource::collection($blogs));
    }

    public function show($id)
    {
        $blog = Blog::where('status', 'published')
            ->where(fn($query) => $query->where('id', $id)->orWhere('slug', $id))
            ->firstOrFail();

        return $this->successResponse('Detail artikel berhasil diambil', new BlogResource($blog));
    }
}
