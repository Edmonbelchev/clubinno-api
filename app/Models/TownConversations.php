<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TownConversations extends Model
{
    use HasFactory;

    protected $fillable = [
        'town_hall_id',
        'performer_id',
        'participation_id',
        'message',
        'date'
    ];

    public function town()
    {
        return $this->belongsTo(TownHall::class);
    }

    public function performer()
    {
        return $this->belongsTo(Performer::class);
    }

    public function participation()
    {
        return $this->belongsTo(TownHallParticipations::class, 'participation_id');
    }

    public function messages()
    {
        return $this->hasMany(TownMessages::class, 'conversation_id');
    }

    public function lastMessage(){
        return $this->hasMany(TownMessages::class, 'conversation_id')
        ->select('message')
        ->latest()
        ->pluck('message')
        ->first();
    }
}
