<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::created(function ($item) {
            if ($item->product) {
                $item->product->decrement('stock', $item->quantity);
            }
        });

        static::deleted(function ($item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        });

        static::updated(function ($item) {
            if ($item->wasChanged('quantity')) {
                $oldQuantity = $item->getOriginal('quantity');
                $newQuantity = $item->quantity;
                $difference = $newQuantity - $oldQuantity;

                if ($difference > 0) {
                    $item->product->decrement('stock', $difference);
                } elseif ($difference < 0) {
                    $item->product->increment('stock', abs($difference));
                }
            }
        });
    }
}
