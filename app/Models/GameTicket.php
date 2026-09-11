<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'token_hash', 'expires_at', 'consumed_at'])]
#[Hidden(['token_hash'])]
class GameTicket extends Model
{
    // HasUuids creates the public lookup portion of a ticket. MassPrunable lets
    // Laravel delete expired rows in efficient batches without loading every
    // GameTicket model into PHP memory first.
    use HasUuids, MassPrunable;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        // The relationship is followed only after all ticket checks pass. The
        // consume endpoint then returns this user's ID and username to the game
        // server as the authenticated socket identity.
        return $this->belongsTo(User::class);
    }

    /**
     * Select ticket rows that the scheduled model:prune command may delete.
     *
     * A consumed ticket is still kept until its expires_at time. Since tickets
     * normally live for only 60 seconds, this provides a brief audit window but
     * guarantees that both used and unused tickets become eligible for cleanup.
     * Deleting an expired row is safe because the consume endpoint rejects it by
     * date even if the scheduler has not deleted the row yet.
     *
     * @return Builder<GameTicket>
     */
    public function prunable(): Builder
    {
        return static::query()->where('expires_at', '<=', now());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // immutable_datetime gives the controller Carbon date objects while
            // preventing accidental in-place changes to persisted timestamps.
            'expires_at' => 'immutable_datetime',
            'consumed_at' => 'immutable_datetime',
        ];
    }
}
