<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
  use HasFactory;


  const STATUS_ACTIVE = 1;
  const STATUS_CANCELLED = 2;


  //using immuatable date here
  //any change in the date object will change the object copy not 
  //the origanl data oject
  //that is the advantage of immutable date 
  protected $casts = [
    'price' => 'integer',
    'status' => 'integer',
    'start_date' => 'immutable_date',
    'end_date' => 'immutable_date',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function office()
  {
    return $this->belongsTo(Office::class);
  }

  public function scopeActiveBetween($query, $from, $to)
  {
      $query->whereStatus(Reservation::STATUS_ACTIVE)
          ->betweenDates($from, $to);
  }

  public function scopeBetweenDates($query, $from, $to)
  {
      $query->where(function ($query) use ($to, $from) {
          $query
              ->whereBetween('start_date', [$from, $to])
              ->orWhereBetween('end_date', [$from, $to])
              ->orWhere(function ($query) use ($to, $from) {
                  $query
                      ->where('start_date', '<', $from)
                      ->where('end_date', '>', $to);
              });
      });
  }
  
}
