<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\CharacterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'status', 'area', 'user_id', 'x', 'y'])]
#[Hidden([])]
class Character extends Model
{
    /** @use HasFactory<CharacterFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'status' => 'string',
            'area' => 'string',
            'name' => 'string',
            'x' => 'integer',
            'y' => 'integer',
        ];
    }

    // load the user that owns this character
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }


}
