<?php

use App\Models\GameTicket;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// GameTicket::prunable() defines which rows are safe to delete. This scheduled
// command checks once per hour and removes every ticket whose expires_at time has
// passed, whether it was consumed or never used. Expiration is still enforced in
// GameTicketController::consume, so a ticket becomes invalid at the exact expiry
// instant; it does not remain valid while waiting for this cleanup task.
//
// Production must run Laravel's scheduler for this declaration to execute. The
// usual deployment runs `php artisan schedule:run` once per minute from cron, or
// keeps `php artisan schedule:work` running as a supervised process.
Schedule::command('model:prune', [
    '--model' => [GameTicket::class],
])->hourly();
