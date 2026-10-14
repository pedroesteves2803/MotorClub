<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magazine_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('primary_color', 7)->default('#ff5a1f');
            $table->string('theme', 20)->default('dark');
            $table->string('editorial_style', 30)->default('editorial');
            $table->foreignId('cover_photo_id')->nullable()->constrained('vehicle_photos')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('vehicle_shot_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shot_photo_id')->constrained()->cascadeOnDelete();
            $table->string('license_snapshot', 120);
            $table->string('credit', 160)->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['vehicle_id', 'shot_photo_id']);
            $table->index(['vehicle_id', 'is_public', 'revoked_at']);
        });

        Schema::create('share_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 40);
            $table->string('source', 40)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
            $table->index(['vehicle_id', 'event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_events');
        Schema::dropIfExists('vehicle_shot_assets');
        Schema::dropIfExists('magazine_settings');
    }
};
