<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_tickets', function (Blueprint $table) {
            // The UUID is safe to expose as the first half of "UUID.secret". It
            // locates a row but cannot authenticate anyone without the secret.
            $table->uuid('id')->primary();

            // Each ticket belongs to the player who requested it. Deleting the
            // user cascades to their tickets so orphan credentials cannot remain.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Oculus stores SHA-256(secret), never the plaintext secret sent to
            // the client. SHA-256 produces exactly 64 hexadecimal characters.
            $table->string('token_hash', 64)->unique();

            // expires_at controls validity and is indexed because the hourly
            // pruning query finds old rows by this column.
            $table->timestamp('expires_at')->index();

            // NULL means unused. The first successful socket authentication sets
            // this timestamp, making every later consumption attempt invalid.
            $table->timestamp('consumed_at')->nullable();

            // created_at and updated_at provide operational/audit context. They
            // do not participate in the validity decision.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_tickets');
    }
};
