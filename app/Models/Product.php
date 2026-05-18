<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'description', 'price', 'stock', 'is_active'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($product) {
            $product->images()->each(function ($image) {
                $image->delete();
            });
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
