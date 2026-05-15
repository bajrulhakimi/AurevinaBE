<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Http\Resources\BlogResource;
use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::latest()->paginate(15);

        return $this->successResponse('Artikel blog berhasil diambil', BlogResource::collection($blogs));
    }

    public function store(StoreBlogRequest $request)
    {
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blogs', 'public');
        }

        $blog = Blog::create($data);

        return $this->successResponse('Artikel blog berhasil dibuat', new BlogResource($blog), 201);
    }

    public function show($id)
    {
        $blog = Blog::findOrFail($id);

        return $this->successResponse('Detail artikel berhasil diambil', new BlogResource($blog));
    }

    public function update(UpdateBlogRequest $request, $id)
    {
        $blog = Blog::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blogs', 'public');
        }

        $blog->update($data);

        return $this->successResponse('Artikel blog berhasil diperbarui', new BlogResource($blog));
    }

    public function destroy($id)
    {
        Blog::findOrFail($id)->delete();

        return $this->successResponse('Artikel blog berhasil dihapus');
    }
}
