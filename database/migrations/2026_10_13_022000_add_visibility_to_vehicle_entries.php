<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_entries', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('cost');
            $table->index(['vehicle_id', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_entries', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id', 'is_public']);
            $table->dropColumn('is_public');
        });
    }
};
