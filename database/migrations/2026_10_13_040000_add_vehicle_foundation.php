<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('vehicle_type', 30)->default('car')->after('user_id');
            $table->string('slug', 180)->nullable()->unique()->after('public_name');
            $table->timestamp('slug_updated_at')->nullable()->after('slug');
            $table->index(['vehicle_type', 'visibility']);
        });

        $used = [];
        DB::table('vehicles')->orderBy('id')->get()->each(function (object $vehicle) use (&$used): void {
            $base = Str::slug(implode('-', array_filter([$vehicle->brand, $vehicle->model, $vehicle->trim])));
            $base = $base !== '' ? $base : 'veiculo';
            if (ctype_digit($base[0] ?? '')) {
                $base = 'veiculo-'.$base;
            }
            $slug = $base;
            $suffix = 2;
            while (isset($used[$slug])) {
                $slug = $base.'-'.$suffix++;
            }
            $used[$slug] = true;
            DB::table('vehicles')->where('id', $vehicle->id)->update([
                'slug' => $slug,
                'slug_updated_at' => now(),
            ]);
        });

        Schema::create('vehicle_slug_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 180)->unique();
            $table->unsignedSmallInteger('redirect_status')->default(301);
            $table->timestamps();
            $table->index(['vehicle_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_slug_histories');
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['vehicle_type', 'visibility']);
            $table->dropUnique(['slug']);
            $table->dropColumn(['vehicle_type', 'slug', 'slug_updated_at']);
        });
    }
};
