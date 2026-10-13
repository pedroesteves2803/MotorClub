<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL may leave this empty table behind if an earlier index creation failed.
        Schema::dropIfExists('shot_download_grants');
        Schema::create('shot_download_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shot_purchase_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shot_photo_id')->constrained()->cascadeOnDelete();
            $table->timestamp('granted_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['shot_purchase_request_id', 'shot_photo_id'], 'shot_grant_payment_photo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shot_download_grants');
    }
};
