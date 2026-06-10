<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_intents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('charger_id')
                ->constrained('chargers')
                ->onDelete('cascade');
            $table->integer('connector');
            $table->integer('amount_cents');
            $table->string('gateway_transaction_id')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('qr_code_base64')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', [
                'pending',
                'paid',
                'expired',
                'cancelled',
            ])->default('pending');
            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};