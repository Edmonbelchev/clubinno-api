<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTownHalls extends Model
{
    use HasFactory;

    protected $table = 'user_town_halls';

    protected $fillable = [
        'user_id',
        'town_hall_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function townHall()
    {
        return $this->belongsTo(TownHall::class);
    }
}
