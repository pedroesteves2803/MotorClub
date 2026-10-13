<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type')->default('enthusiast');
            $table->text('bio')->nullable();
            $table->string('city', 100)->nullable();
            $table->boolean('profile_public')->default(false);
        });
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('city', 100);
            $table->string('state', 2);
            $table->string('specialties', 250);
            $table->text('description');
            $table->string('whatsapp', 13)->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workshop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('workshop_status')->default('pending');
            $table->string('title', 150);
            $table->string('category', 50);
            $table->string('status')->default('planning');
            $table->text('description');
            $table->text('parts')->nullable();
            $table->text('services')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->boolean('show_cost')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
        Schema::create('project_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject', 150);
            $table->text('message');
            $table->string('status')->default('pending');
            $table->text('reply')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->timestamps();
        });
        Schema::create('car_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->string('category', 50);
            $table->dateTime('starts_at');
            $table->string('city', 100);
            $table->string('state', 2);
            $table->string('venue', 200);
            $table->string('organizer', 120);
            $table->text('description');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['car_events', 'quote_requests', 'project_photos', 'projects', 'workshops'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['account_type', 'bio', 'city', 'profile_public']));
    }
};
