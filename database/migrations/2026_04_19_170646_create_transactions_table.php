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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('charger_id')
                ->constrained('chargers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
	        $table->foreignId('rfid_card_id')
                ->nullable()
                ->constrained('rfid_cards')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('meter_start')->nullable();
            $table->integer('meter_stop')->nullable();
            $table->integer('total_cost')->nullable(); //cents
            $table->integer('paid_amount')->nullable(); //cents
            $table->decimal('energy_kwh', 10, 3)->nullable();
            $table->string('stop_reason')->nullable();
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
