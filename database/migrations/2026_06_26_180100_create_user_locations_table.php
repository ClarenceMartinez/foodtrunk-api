<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_locations', function (Blueprint $table) {
            $table->id();
            // unique(): este sistema guarda la ULTIMA ubicacion conocida,
            // no un historial. POST /api/me/location hace updateOrCreate.
            // Si en el futuro se necesita historial de movimiento, se
            // puede crear una tabla aparte (ej. user_location_history)
            // sin tocar esta.
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_locations');
    }
};
