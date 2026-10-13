<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('public_name', 120)->nullable()->after('model');
            $table->string('generation', 50)->nullable()->after('trim');
            $table->string('color', 50)->nullable()->after('generation');
            $table->unsignedInteger('mileage')->nullable()->after('engine');
            $table->boolean('mileage_approximate')->default(false)->after('mileage');
            $table->date('acquired_on')->nullable()->after('mileage_approximate');
            $table->string('location', 100)->nullable()->after('acquired_on');
            $table->string('instagram_url', 255)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'public_name', 'generation', 'color', 'mileage', 'mileage_approximate',
                'acquired_on', 'location', 'instagram_url',
            ]);
        });
    }
};
