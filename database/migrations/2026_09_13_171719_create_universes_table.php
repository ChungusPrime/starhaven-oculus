<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {

        Schema::create('universes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('address');
            $table->integer('players');
            $table->datetime('last_refresh');
            $table->boolean('online');
        });

        collect(config("oculus.universes"))->each(function ($universe) {
            DB::table('universes')->insert([
                'name' => $universe['name'],
                'address' => $universe['address'] ?? '',
                'players' => $universe['players'] ?? 0,
                'online' => $universe['online'] ?? false,
                'last_refresh' => now(),
                'created_at' => now(), 
                'updated_at' => now(),
            ]);
        });

    }

    /**
     * Reverse the migrations.
     *
     * This will drop the 'universes' table, effectively undoing the schema changes
     * made in the up() method.
     */
    public function down(): void {
        Schema::dropIfExists('universes');
    }
};
