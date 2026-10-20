<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discovery extends Model
{
    protected $fillable = [
        'name',
        'data',
        'universe_id',
        'user_id',
    ];

    public function universe()
    {
        return $this->belongsTo(Universe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
