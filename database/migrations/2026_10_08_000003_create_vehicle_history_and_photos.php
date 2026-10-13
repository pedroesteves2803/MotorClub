<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('title', 150);
            $table->text('description');
            $table->date('occurred_on');
            $table->text('parts')->nullable();
            $table->text('services')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->timestamps();
            $table->index(['vehicle_id', 'occurred_on']);
        });
        Schema::create('vehicle_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('path')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_photos');
        Schema::dropIfExists('vehicle_entries');
    }
};
