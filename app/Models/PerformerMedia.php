<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformerMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'performer_id',
        'media'
    ];

    public function performer()
    {
        return $this->belongsTo(Performer::class);
    }
}
