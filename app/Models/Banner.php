<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $guarded = [

    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
