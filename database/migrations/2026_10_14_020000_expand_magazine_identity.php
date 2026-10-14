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
            $table->string('public_key', 180)->nullable()->unique()->after('slug');
        });

        $used = [];
        DB::table('vehicles')->orderBy('id')->each(function (object $vehicle) use (&$used): void {
            $base = Str::slug((string) ($vehicle->slug ?: trim(($vehicle->brand ?? '').' '.($vehicle->model ?? ''))));
            $base = $base !== '' ? Str::limit($base, 160, '') : 'veiculo';
            $candidate = $base;
            $suffix = 2;

            while (in_array($candidate, $used, true) || DB::table('vehicles')->where('public_key', $candidate)->exists()) {
                $candidate = Str::limit($base, 150, '').'-'.$suffix++;
            }

            $used[] = $candidate;
            DB::table('vehicles')->where('id', $vehicle->id)->update(['public_key' => $candidate]);
        });

        Schema::table('magazine_settings', function (Blueprint $table) {
            $table->string('title', 160)->nullable()->after('editorial_style');
            $table->string('subtitle', 240)->nullable()->after('title');
            $table->string('palette', 30)->default('carbon')->after('subtitle');
            $table->boolean('is_published')->default(true)->after('palette');
            $table->json('highlight_photo_ids')->nullable()->after('cover_photo_id');
            $table->json('chapter_order')->nullable()->after('highlight_photo_ids');
            $table->string('qr_foreground', 7)->default('#111111')->after('chapter_order');
            $table->string('qr_background', 7)->default('#ffffff')->after('qr_foreground');
            $table->string('qr_label', 120)->nullable()->after('qr_background');
            $table->string('qr_brand', 120)->nullable()->after('qr_label');
        });
    }

    public function down(): void
    {
        Schema::table('magazine_settings', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'subtitle', 'palette', 'is_published', 'highlight_photo_ids',
                'chapter_order', 'qr_foreground', 'qr_background', 'qr_label', 'qr_brand',
            ]);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique(['public_key']);
            $table->dropColumn('public_key');
        });
    }
};
