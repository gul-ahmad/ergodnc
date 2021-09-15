<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use HasFactory,SoftDeletes;

    const APPROVAL_PENDING = 1;
    const APPROVAL_APPROVED = 2;
    const APPROVAL_REJECTED = 3;

    //Gul here
    //We are using cast here 
    //whenever database return values they are mostly
    //strings so we are using casting here
    //so whenever this fields are returned they should be casted
    //to the following types
    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'approval_status' => 'integer',
        'hidden' => 'bool',
        'price_per_day' => 'integer',
        'monthly_discount' => 'integer',
    ];

    public function user()
    {
     return $this->belongsTo(User::class);
    }
    public function reservations()
    {

     return $this->hasMany(Reservation::class);

    }
    public function images()
    {

      return $this->morphMany(Image::class ,'resource');


    }
}
