<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_photos', function (Blueprint $table) {
            $table->string('kind', 30)->default('galeria')->after('path');
            $table->string('caption', 255)->nullable()->after('kind');
            $table->unsignedInteger('sort_order')->default(0)->after('caption');
            $table->boolean('is_primary')->default(false)->after('sort_order');
        });

        Schema::table('project_photos', function (Blueprint $table) {
            $table->string('kind', 30)->default('editorial')->after('path');
            $table->string('caption', 255)->nullable()->after('kind');
            $table->unsignedInteger('sort_order')->default(0)->after('caption');
            $table->boolean('is_primary')->default(false)->after('sort_order');
        });

        Schema::create('project_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->string('category', 50)->default('outro');
            $table->string('status', 30)->default('planned');
            $table->text('description')->nullable();
            $table->date('started_on')->nullable();
            $table->date('completed_on')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->index(['project_id', 'sort_order']);
        });

        Schema::create('project_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_modification_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('brand', 100)->nullable();
            $table->string('model', 120)->nullable();
            $table->string('status', 30)->default('planned');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->index(['project_modification_id', 'sort_order']);
        });

        Schema::create('media_photos', function (Blueprint $table) {
            $table->id();
            $table->morphs('imageable');
            $table->string('path')->unique();
            $table->string('kind', 30)->default('galeria');
            $table->string('caption', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->index(['imageable_type', 'imageable_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_photos');
        Schema::dropIfExists('project_parts');
        Schema::dropIfExists('project_modifications');
        Schema::table('project_photos', function (Blueprint $table) {
            $table->dropColumn(['kind', 'caption', 'sort_order', 'is_primary']);
        });
        Schema::table('vehicle_photos', function (Blueprint $table) {
            $table->dropColumn(['kind', 'caption', 'sort_order', 'is_primary']);
        });
    }
};
