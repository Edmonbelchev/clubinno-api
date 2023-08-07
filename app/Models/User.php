<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'phone'
    ];

    /* ADDITIONAL ATTRIBUTES */
    protected $appends = [
        'total_profiles',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function entertainment_places()
    {
        return $this->belongsToMany(EntertainmentPlace::class, 'user_entertainment_places')->withTimestamps();
    }

    public function performers(){
        return $this->belongsToMany(Performer::class, 'user_performers')->withTimestamps();
    }

    public function companies(){
        return $this->belongsToMany(Companies::class, 'user_companies', 'user_id', 'company_id')->withTimestamps();
    }

    public function town_halls(){
        return $this->belongsToMany(TownHall::class, 'user_town_halls', 'user_id', 'town_hall_id')->withTimestamps();
    }

    public function getTotalProfilesAttribute(){
        if ($this->type == 1) {
            return $this->entertainment_places->count();
        }
        
        elseif($this->type == 2){
            return $this->performers->count();

        }
        
        elseif($this->type == 3){
            return $this->companies->count();
        }
        
        elseif($this->type == 4){
            return $this->town_halls->count();
        }
    }
}
