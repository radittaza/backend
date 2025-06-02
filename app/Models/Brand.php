<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
      'brand_name',
      'public_url_image',
      'secure_url_image',
    ];


    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
