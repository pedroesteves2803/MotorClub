<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photographer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('professional_name', 120);
            $table->text('description');
            $table->string('city', 100);
            $table->string('state', 2);
            $table->string('instagram', 100)->nullable();
            $table->string('website', 255)->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
        Schema::create('shot_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_event_id')->nullable()->constrained('car_events')->nullOnDelete();
            $table->string('title', 150);
            $table->text('description');
            $table->string('city', 100);
            $table->string('state', 2);
            $table->date('shot_on');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
        Schema::create('shot_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shot_gallery_id')->constrained()->cascadeOnDelete();
            $table->string('original_path');
            $table->string('preview_path');
            $table->string('brand', 80)->nullable();
            $table->string('model', 80)->nullable();
            $table->string('color', 50)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('license', 120)->default('Uso pessoal na MotorClub');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
        Schema::create('shot_purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shot_photo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->text('message')->nullable();
            $table->decimal('quoted_price', 12, 2)->nullable();
            $table->decimal('photographer_amount', 12, 2)->nullable();
            $table->decimal('platform_amount', 12, 2)->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamps();
            $table->unique(['shot_photo_id', 'buyer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shot_purchase_requests');
        Schema::dropIfExists('shot_photos');
        Schema::dropIfExists('shot_galleries');
        Schema::dropIfExists('photographer_profiles');
    }
};
