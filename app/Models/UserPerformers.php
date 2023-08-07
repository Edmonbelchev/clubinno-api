<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPerformers extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'performer_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function performer()
    {
        return $this->belongsTo(Performer::class);
    }
}
