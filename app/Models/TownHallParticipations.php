<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TownHallParticipations extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'performer_id',
        'town_hall_id',
        'time',
        'date',
        'notes',
        'message',
        'honorarium',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at',
        'created_at',
        'deleted_at'
    ];

    public function performer()
    {
        return $this->hasOne(Performer::class, 'id', 'performer_id');
    }

    public function town_hall()
    {
        return $this->belongsTo(TownHall::class);
    }

    public function conversations(){
        return $this->hasMany(TownConversations::class, 'participation_id');
    }

    public function notes(){
        return $this->hasMany(TownParticipationNotes::class, 'participation_id');
    }

    public function latestNote($id){
        return $this->hasMany(TownParticipationNotes::class, 'participation_id')
        ->select('note')
        ->where('user_id', $id)
        ->latest()
        ->pluck('note')
        ->first();
    }
}
