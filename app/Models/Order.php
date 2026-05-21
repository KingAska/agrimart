<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
    'invoice_number',
    'customer_name',
    'customer_email',
    'customer_phone',
    'customer_address',
    'total_price',
    'payment_method',
    'payment_status',
    'status',
    'snap_token',
    'latitude',
    'longitude',
    'note',
    'shipping_cost',
    'delivery_type',
];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted()
    {
        static::updated(function ($order) {
            if ($order->wasChanged('status')) {
                $oldStatus = $order->getOriginal('status');
                $newStatus = $order->status;

                if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $item->product->increment('stock', $item->quantity);
                        }
                    }
                }

                if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $item->product->decrement('stock', $item->quantity);
                        }
                    }
                }
            }
        });

        static::deleting(function ($order) {
            if ($order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            }
        });
    }
}
