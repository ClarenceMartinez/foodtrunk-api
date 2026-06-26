<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('push_notifications_enabled')->default(true);
            $table->boolean('smart_nearby_alerts_enabled')->default(true);
            $table->boolean('location_alerts_enabled')->default(true);
            $table->boolean('promotion_alerts_enabled')->default(true);
            $table->unsignedTinyInteger('max_alerts_per_day')->default(3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification_settings');
    }
};
