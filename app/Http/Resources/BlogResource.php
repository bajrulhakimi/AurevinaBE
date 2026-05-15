<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BlogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => Str::limit(strip_tags($this->content), 150),
            'category' => 'Aurevina Tips',
            'read_time' => max(2, (int) ceil(str_word_count(strip_tags($this->content)) / 180)) . ' menit baca',
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'author' => [
                'id' => $this->author?->id,
                'name' => $this->author?->name,
            ],
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
