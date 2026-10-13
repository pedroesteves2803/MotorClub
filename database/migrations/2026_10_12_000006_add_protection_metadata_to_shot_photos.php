<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shot_photos', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('original_sha256', 64)->nullable()->after('preview_path');
            $table->string('processing_status', 20)->default('ready')->after('license');
            $table->text('processing_error')->nullable()->after('processing_status');
            $table->timestamp('published_at')->nullable()->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('shot_photos', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn(['uuid', 'original_sha256', 'processing_status', 'processing_error', 'published_at']);
        });
    }
};
