<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['username', 'password'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable {

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['username', 'password', 'missions', 'achievements', 'storage', 'settings', 'ownership'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'missions' => 'array',
            'achievements' => 'array',
            'storage' => 'array',
            'settings' => 'array',
            'ownership' => 'array',
            'scrip' => 'integer',
            'characters' => 'array',
            'discoveries' => 'array',
        ];
    }

    // lazy load the user's characters
    public function characters(): HasMany {
        return $this->hasMany(Character::class);
    }

    public function activeCharacter(): HasOne {
        return $this->hasOne(Character::class)->where('status', 'active');
    }

    public function characterHistory(): HasMany {
        return $this->hasMany(Character::class)->where('status', 'inactive');
    }

    public function discoveries(): HasMany {
        return $this->hasMany(Discovery::class);
    }

}
