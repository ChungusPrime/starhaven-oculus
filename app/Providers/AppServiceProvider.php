<?php

namespace App\Providers;

use App\Database\Schema\Grammars\AzureSqlServerGrammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $connection = DB::connection();

        if ($connection->getDriverName() === 'sqlsrv') {
            $connection->setSchemaGrammar(new AzureSqlServerGrammar($connection));
        }
    }
}
