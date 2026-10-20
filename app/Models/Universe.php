<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Universe extends Model {

    use HasFactory;

    protected $fillable = [
        'name',
        'players',
        'address',
        'last_refresh',
        'online',
    ];

    protected function casts(): array {
        return [
            'players' => 'integer',
            'address' => 'string',
            'last_refresh' => 'datetime',
            'online' => 'boolean',
        ];
    }

}
