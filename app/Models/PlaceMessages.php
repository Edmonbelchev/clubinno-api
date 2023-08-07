<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceMessages extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
        'message',
        'readed',
        'updated_at',
        'created_at',
    ];

    public function place()
    {
        return $this->belongsTo(PlaceConversations::class);
    }
}
