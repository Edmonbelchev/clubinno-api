<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEntertainmentPlaces extends Model
{
    use HasFactory;

    protected $table = 'user_entertainment_places';

    protected $fillable = [
        'user_id',
        'entertainment_place_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entertainmentPlace()
    {
        return $this->belongsTo(EntertainmentPlace::class);
    }
}
