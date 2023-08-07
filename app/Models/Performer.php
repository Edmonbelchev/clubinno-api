<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Performer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'youtube',
        'spotify',
        'phone',
        'genre',
        'image'
    ];

     /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at',
        'created_at',
    ];

    public function getImageAttribute($value)
    {
        if (!$value) {
            // If 'img' is null, return the path to the default image
            return asset('/public/images/profile-image.webp');
        }

        // Otherwise, return the actual image path
        return $value;
    }

    public function getGenreAttribute($value)
    {
        if($value == 0){
            return '';
        }
        
        $value = MusicGenre::select('name')->where('id', $value)->pluck('name')->first();

        // Otherwise, return genre's value
        return $value;
    }

    /* USE THIS IF USER IS OF TYPE 2 (PERFORMER) */
    public function user()
    {
        return $this->belongsToMany(User::class, 'user_performers')->withTimestamps();
    }

    public function userType(){
        return $this->belongsToMany(
            User::class,
            'user_performers',
            'performer_id',
            'user_id'
        )->where('users.type', 2)->select('users.type')->pluck('type')->first();
    }
 
     /* USE THIS IF USER IS OF TYPE 2 (Performer ETC...) */
     public function mainUser(){
         return $this->belongsToMany(
             User::class,
             'user_performers',
             'performer_id',
             'user_id'
         )->where('users.type', 2)->select('users.id')->pluck('id');
     }

    /* USE THIS IF USER IS OF TYPE 5 - PERSONNEL */
    public function personnel(){
        return $this->belongsToMany(
            User::class,
            'user_performers',
            'performer_id',
            'user_id'
        )->where('users.type', 5)->get();
    }

    public function medias()
    {
        return $this->hasMany(PerformerMedia::class)->select('id', 'media')->orderBy('created_at', 'desc');
    }

    public function genres()
    {
        return MusicGenre::whereIn('id', explode(',', $this->genre))->get();
    }

    public function totalParticipations(){
        $currentDate = Carbon::now()->subDay();
        $total = $this->hasMany(PlaceParticipations::class)->where('date', '>', $currentDate)->count();

        if ($total > 0) return $total;
    }
}