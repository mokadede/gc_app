<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code', 'created_by', 'customer_name', 'customer_phone',
        'pickup_address', 'notes', 'status', 'pickup_time', 'estimated_done',
        'total_price', 'is_paid', 'payment_method',
        'voucher_id', 'discount_amount',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'pickup_time' => 'datetime',
        'estimated_done' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
