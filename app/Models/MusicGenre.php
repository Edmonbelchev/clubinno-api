<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MusicGenre extends Model
{
    use HasFactory;

    public function performers()
    {
        return Performer::select('id', 'name', 'image')
        ->where('performers.genre', $this->id)
        ->take(4)
        ->get();
    }
}
