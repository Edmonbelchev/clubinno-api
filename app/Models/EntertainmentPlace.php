<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntertainmentPlace extends Model
{
    use HasFactory;

    protected $table = 'entertainment_place';

    protected $fillable = [
        'name',
        'city',
        'address',
        'phone',
        'type',
        'image'
    ];

    /* USE THIS IF USER IS OF TYPE 5 (PERSONNEL) */
    public function user()
    {
        return $this->belongsToMany(User::class, 'user_entertainment_places')->withTimestamps();
    }

    public function userType(){
        return $this->belongsToMany(
            User::class,
            'user_entertainment_places',
            'entertainment_place_id',
            'user_id'
        )->where('users.type', 1)->select('users.type')->pluck('type')->first();
    }

    /* USE THIS IF USER IS OF TYPE 1 (CLUB ETC...) */
    public function mainUser(){
        return $this->belongsToMany(
            User::class,
            'user_entertainment_places',
            'entertainment_place_id',
            'user_id'
        )->where('users.type', 1)->select('users.id')->pluck('id');
    }

    /* USE THIS IF USER IS OF TYPE 5 - PERSONNEL */
    public function personnel(){
        return $this->belongsToMany(
            User::class,
            'user_entertainment_places',
            'entertainment_place_id',
            'user_id'
        )->where('users.type', 5)->get();
    }

    public function participations(){
        return $this->hasMany(PlaceParticipations::class, 'entertainment_id');
    }

    public function unreadMessages(){
        return $this->hasMany(PlaceConversations::class, 'entertainment_id')
        ->join('place_messages', 'place_messages.conversation_id', '=', 'place_conversations.id')
        ->where('place_messages.readed', 0)
        ->where('place_messages.sender_type', 2)
        ->where('place_messages.sender_id', '!=', $this->id)
        ->count();
    }

    public function conversation($id){
        return $this->hasMany(PlaceConversations::class, 'entertainment_id')
        ->select('id')
        ->where('participation_id', $id)
        ->pluck('id')
        ->first();
    }
}
