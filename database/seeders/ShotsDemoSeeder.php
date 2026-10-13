<?php

namespace Database\Seeders;

use App\Models\PhotographerProfile;
use App\Models\ShotGallery;
use App\Models\User;
use App\Services\ShotImages;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ShotsDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demonstração apenas em ambiente local ou testes.');
        }
        $photographer = User::firstOrNew(['email' => 'shots-demo@motorclub.example']);
        $photographer->forceFill([
            'name' => 'MotorClub Shots · Demonstração',
            'password' => Hash::make(Str::random(48)),
            'email_verified_at' => now(),
            'is_demo' => true,
            'profile_public' => true,
            'city' => 'São Paulo',
            'bio' => 'Portfólio fictício com imagens geradas para demonstrar o MotorClub Shots.',
        ])->save();
        PhotographerProfile::updateOrCreate(['user_id' => $photographer->id], [
            'professional_name' => 'MotorClub Shots · Demonstração',
            'description' => 'Galerias fictícias com imagens geradas por IA para testar a experiência visual. Não representam trabalhos, eventos ou carros reais.',
            'city' => 'São Paulo',
            'state' => 'SP',
            'is_public' => true,
        ]);

        $photos = [
            ['title' => 'Luz dourada · Demonstração', 'description' => 'Imagem gerada por IA para demonstrar prévias protegidas do Shots. Não representa veículo ou encontro real.', 'file' => 'golf-sunset.png', 'brand' => 'Volkswagen', 'model' => 'Golf', 'color' => 'Azul', 'price' => 15],
            ['title' => 'Asfalto molhado · Demonstração', 'description' => 'Imagem gerada por IA para demonstrar prévias protegidas do Shots. Não representa veículo ou encontro real.', 'file' => 'coupe-night.png', 'brand' => 'Coupé clássico', 'model' => 'Esportivo', 'color' => 'Grafite', 'price' => 20],
            ['title' => 'Linha de corrida · Demonstração', 'description' => 'Imagem gerada por IA para demonstrar prévias protegidas do Shots. Não representa veículo ou autódromo real.', 'file' => 'track-white.png', 'brand' => 'Coupé esportivo', 'model' => 'Track', 'color' => 'Branco', 'price' => null],
        ];
        $images = app(ShotImages::class);
        foreach ($photos as $item) {
            $gallery = ShotGallery::updateOrCreate(['user_id' => $photographer->id, 'title' => $item['title']], [
                'description' => $item['description'],
                'city' => 'São Paulo',
                'state' => 'SP',
                'shot_on' => now()->toDateString(),
                'is_public' => true,
            ]);
            if ($gallery->photos()->exists()) {
                continue;
            }
            $file = new UploadedFile(base_path('database/seeders/assets/shots/'.$item['file']), $item['file'], 'image/png', null, true);
            $uuid = (string) Str::uuid();
            $paths = $images->store($file, $uuid, 'MotorClub Shots · Demonstração');
            $gallery->photos()->create($paths + [
                'uuid' => $uuid,
                'brand' => $item['brand'],
                'model' => $item['model'],
                'color' => $item['color'],
                'price' => $item['price'],
                'license' => 'Demonstração visual — sem licença comercial',
                'is_published' => true,
                'processing_status' => 'ready',
            ]);
        }
    }
}
