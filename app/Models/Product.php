<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'product_name',
        'slug',
        'sku',
        'description',
        'total_stock',
        'base_price',
        'special_price',
        'special_start_date',
        'special_end_date',
        'weight',
        'main_image',
        'status',
        'show_on_hero',
        'hero_position',
    ];

    protected $casts = [
        'total_stock' => 'integer',
        'base_price' => 'decimal:2',
        'special_price' => 'decimal:2',
        'special_start_date' => 'date',
        'special_end_date' => 'date',
        'weight' => 'decimal:2',
        'show_on_hero' => 'boolean',
        'hero_position' => 'integer',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'active');
    }

    public function hasActiveSpecialPrice(): bool
    {
        if (!$this->special_price || (float) $this->special_price <= 0 || (float) $this->special_price >= (float) $this->base_price) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->special_start_date && $this->special_start_date->toDateString() > $today) {
            return false;
        }

        if ($this->special_end_date && $this->special_end_date->toDateString() < $today) {
            return false;
        }

        return true;
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->hasActiveSpecialPrice() ? (float) $this->special_price : (float) $this->base_price;
    }
}
