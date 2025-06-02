<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'vehicle_type',
        'vehicle_name',
        'rental_price',
        'availability_status',
        'year',
        'seats',
        'horse_power',
        'description',
        'specification_list',
        'secure_url_image',
        'public_url_image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'year' => 'datetime',

    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function banners()
    {
        return $this->hasMany(Banner::class);
    }
}
