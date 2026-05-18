<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Storage;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image_path'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            if ($image->image_path) {
                Storage::disk('public')->delete($image->image_path);
            }
        });

        static::updating(function ($image) {
            if ($image->isDirty('image_path')) {
                $oldPath = $image->getOriginal('image_path');
                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
