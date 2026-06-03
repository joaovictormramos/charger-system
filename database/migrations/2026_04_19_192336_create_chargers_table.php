<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chargers', function (Blueprint $table) {
            $table->id();
	        $table->string('identifier')->unique();
            $table->string('name')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', [
                'Available', 
                'Preparing', 
                'Charging', 
                'Faulted', 
                'Unavailable',
            ])->default('Unavailable');
            $table->integer('price_per_kwh'); //cents
            $table->timestamp('last_heartbeat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('chargers');
        Schema::enableForeignKeyConstraints();
    }
};
