<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_truck_id')->nullable()->constrained('food_trucks')->nullOnDelete();
            $table->string('type'); // promotion_created, smart_nearby_promotion, etc.
            $table->timestamp('sent_at');
            $table->timestamps();

            // Estos dos indices son los que hacen rapidas las consultas de
            // "cuantas alertas lleva hoy" y "ya le mande algo de este
            // food truck hoy", que se corren en CADA promocion/evento
            // creado para CADA seguidor.
            $table->index(['user_id', 'sent_at']);
            $table->index(['user_id', 'food_truck_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
