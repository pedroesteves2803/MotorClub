<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_entries', function (Blueprint $table) {
            $table->date('occurred_on')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_entries', function (Blueprint $table) {
            $table->date('occurred_on')->nullable(false)->change();
        });
    }
};
