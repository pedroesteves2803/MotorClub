<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('brand', 80);
            $table->string('model', 80);
            $table->string('trim', 100);
            $table->unsignedSmallInteger('model_year');
            $table->string('engine', 100);
            $table->string('visibility')->default('private');
            $table->timestamps();
            $table->index(['brand', 'model', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
