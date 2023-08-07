<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceConversations extends Model
{
    use HasFactory;

    protected $fillable = [
        'entertainment_id',
        'performer_id',
        'participation_id',
        'message',
        'date'
    ];

    public function place()
    {
        return $this->belongsTo(EntertainmentPlace::class);
    }

    public function performer()
    {
        return $this->belongsTo(Performer::class);
    }

    public function participation()
    {
        return $this->belongsTo(PlaceParticipations::class, 'participation_id');
    }

    public function messages()
    {
        return $this->hasMany(PlaceMessages::class, 'conversation_id');
    }

    public function lastMessage(){
        return $this->hasMany(PlaceMessages::class, 'conversation_id')
        ->select('message')
        ->latest()
        ->pluck('message')
        ->first();
    }
}
