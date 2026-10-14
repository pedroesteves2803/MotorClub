<?php

namespace App\Http\Controllers;

use App\Models\CarEvent;
use App\Models\FeatureSetting;
use App\Models\Project;
use App\Models\ShotGallery;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Workshop;
use App\Services\VehicleSlugService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /central-mc-gestao',
            'Disallow: /configuracoes',
            'Disallow: /meu-perfil',
            'Disallow: /garagem',
            'Disallow: /projetos/novo',
            'Disallow: /projetos/*/editar',
            'Disallow: /projetos/*/relatorios/*/editar',
            'Disallow: /shots/solicitacoes',
            'Disallow: /meus-shots',
            'Disallow: /orcamentos',
            'Disallow: /assinatura',
            'Disallow: /stripe',
            'Disallow: /como-testar',
            'Disallow: /shots/fotos/*/download',
            'Sitemap: '.route('seo.sitemap'),
            '',
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $urls = collect([
            ['loc' => route('home')],
            ['loc' => route('explore')],
            ['loc' => route('projects.index')],
            ['loc' => route('shots.index')],
            ['loc' => route('legal.terms')],
            ['loc' => route('legal.privacy')],
            ['loc' => route('legal.cookies')],
            ['loc' => route('legal.community')],
            ['loc' => route('legal.help')],
        ]);

        Vehicle::query()
            ->where('visibility', 'public')
            ->whereHas('user', fn ($query) => $query->where('is_demo', false))
            ->get(['id', 'updated_at', 'user_id'])
            ->each(function (Vehicle $vehicle) use ($urls) {
                $urls->push(['loc' => route('vehicles.slug.show', ['slug' => app(VehicleSlugService::class)->ensure($vehicle)]), 'lastmod' => $vehicle->updated_at]);
            });

        Project::query()
            ->published()
            ->get(['id', 'updated_at'])
            ->each(function (Project $project) use ($urls) {
                $urls->push(['loc' => route('projects.show', $project), 'lastmod' => $project->updated_at]);
            });

        User::query()
            ->where('profile_public', true)
            ->where('is_demo', false)
            ->get(['id', 'updated_at'])
            ->each(function (User $person) use ($urls) {
                $urls->push(['loc' => route('profiles.show', $person), 'lastmod' => $person->updated_at]);
            });

        ShotGallery::query()
            ->where('is_public', true)
            ->whereHas('user.photographerProfile', fn ($query) => $query->where('is_public', true))
            ->get(['id', 'updated_at'])
            ->each(function (ShotGallery $gallery) use ($urls) {
                $urls->push(['loc' => route('shots.galleries.show', $gallery), 'lastmod' => $gallery->updated_at]);
            });

        if (FeatureSetting::enabled('events')) {
            CarEvent::query()
                ->where('is_public', true)
                ->where('starts_at', '>=', now())
                ->get(['id', 'updated_at'])
                ->each(function (CarEvent $event) use ($urls) {
                    $urls->push(['loc' => route('events.show', $event), 'lastmod' => $event->updated_at]);
                });
        }

        if (FeatureSetting::enabled('workshops')) {
            Workshop::query()
                ->where('is_public', true)
                ->get(['id', 'updated_at'])
                ->each(function (Workshop $workshop) use ($urls) {
                    $urls->push(['loc' => route('workshops.show', $workshop), 'lastmod' => $workshop->updated_at]);
                });
        }

        $xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        foreach ($urls as $url) {
            $lastmod = $url['lastmod'] ?? null;
            $xml[] = '  <url><loc>'.e($url['loc']).'</loc>'.($lastmod ? '<lastmod>'.$lastmod->toAtomString().'</lastmod>' : '').'</url>';
        }
        $xml[] = '</urlset>';

        return response(implode("\n", $xml), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
