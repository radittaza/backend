<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'rental_period',
        'start_date',
        'end_date',
        'delivery_location',
        'rental_status',
        'total_price',
        'secure_url_image',
        'public_url_image',
        'payment_proof',
        'bank_transfer',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',

    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(Address::class, 'delivery_location');
    }

    public function bank()
    {
        return $this->belongsTo(BankTransfer::class, 'bank_transfer');
    }
}
