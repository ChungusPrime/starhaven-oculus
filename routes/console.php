<?php

use App\Models\GameTicket;
use App\Models\Universe;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () { $this->comment(Inspiring::quote()); })->purpose('Display an inspiring quote');

// GameTicket::prunable() defines which rows are safe to delete. This scheduled
// command checks once per hour and removes every ticket whose expires_at time has
// passed, whether it was consumed or never used. Expiration is still enforced in
// GameTicketController::consume, so a ticket becomes invalid at the exact expiry
// instant; it does not remain valid while waiting for this cleanup task.
//
// Production must run Laravel's scheduler for this declaration to execute. The
// usual deployment runs `php artisan schedule:run` once per minute from cron, or
// keeps `php artisan schedule:work` running as a supervised process.
Schedule::command('model:prune', ['--model' => [GameTicket::class],])->hourly();

Schedule::call(function () {

    collect(config("oculus.universes"))->each(function ($universe) {

        try {

            $uni = Universe::firstOrCreate(
                ['name' => $universe['name']],
                ['players' => 0, 'online' => false, 'last_refresh' => now()],
            );

            $url = rtrim($universe['address'], '/') . '/server_status';

            $response = Http::connectTimeout(config('oculus.universe_status_connect_timeout_seconds'))
            ->timeout(config('oculus.universe_status_timeout_seconds'))
            ->retry(3, 500)
            ->get($url);

            if ($response->successful()) {
                $uni->update([
                    'players' => (int) $response->json('players', 0),
                    'last_refresh' => now(),
                    'online' => true,
                ]);
            } else {
                $uni->update([
                    'online' => false,
                    'last_refresh' => now(),
                ]);
            }
        } catch (Throwable $exception) {
            Log::error('Universe request encountered an error', [
                'universe' => $universe['name'],
                'message' => $exception->getMessage(),
            ]);
            if (isset($uni)) {
                $uni->update([
                    'online' => false,
                    'last_refresh' => now(),
                ]);
            }
        }

    });

})
->name('universes:refresh')
->everyMinute()
->withoutOverlapping();
