<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shot_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shot_photo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 120);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();
            $table->unique(['shot_photo_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shot_reports');
    }
};
