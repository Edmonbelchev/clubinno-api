<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TownHall extends Model
{
    use HasFactory;

    protected $table = 'town_hall';

    protected $fillable = [
        'name',
        'city',
        'address',
        'phone',
        'image'
    ];

    /* USE THIS IF USER IS OF TYPE 5 (PERSONNEL) */
    public function user()
    {
        return $this->belongsToMany(User::class, 'user_town_halls')->withTimestamps();
    }

    public function userType(){
        return $this->belongsToMany(
            User::class,
            'user_town_halls',
            'town_hall_id',
            'user_id'
        )->where('users.type', 4)->select('users.type')->pluck('type')->first();
    }

    /* USE THIS IF USER IS OF TYPE 4 ( TOWN HALL ETC...) */
    public function mainUser(){
        return $this->belongsToMany(
            User::class,
            'user_town_halls',
            'town_hall_id',
            'user_id'
        )->where('users.type', 4)->select('users.id')->pluck('id');
    }

    /* USE THIS IF USER IS OF TYPE 5 - PERSONNEL */
    public function personnel(){
        return $this->belongsToMany(
            User::class,
            'user_town_halls',
            'town_hall_id',
            'user_id'
        )->where('users.type', 5)->get();
    }

    public function participations(){
        return $this->hasMany(TownHallParticipations::class, 'town_hall_id');
    }

    public function unreadMessages(){
        return $this->hasMany(TownConversations::class, 'town_hall_id')
        ->join('town_messages', 'town_messages.conversation_id', '=', 'town_conversations.id')
        ->where('town_messages.readed', 0)
        ->where('town_messages.sender_type', 2)
        ->where('town_messages.sender_id', '!=', $this->id)
        ->count();
    }

    public function conversation($id){
        return $this->hasMany(TownConversations::class, 'town_hall_id')
        ->select('id')
        ->where('participation_id', $id)
        ->pluck('id')
        ->first();
    }
}
