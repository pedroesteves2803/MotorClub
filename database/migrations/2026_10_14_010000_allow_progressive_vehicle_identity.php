<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('trim', 100)->nullable()->change();
            $table->string('engine', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('trim', 100)->default('')->nullable(false)->change();
            $table->string('engine', 100)->default('')->nullable(false)->change();
        });
    }
};
