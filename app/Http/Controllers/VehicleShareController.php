<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleSlugHistory;
use App\Services\MagazineService;
use App\Services\QrCodeService;
use App\Services\ShareEventLogger;
use App\Services\VehicleSlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class VehicleShareController extends Controller
{
    public function edit(Vehicle $vehicle, MagazineService $magazines, VehicleSlugService $slugs): Response
    {
        Gate::authorize('update', $vehicle);

        $slugs->ensure($vehicle);

        return response()->view('vehicles.share', [
            'vehicle' => $vehicle,
            'publicUrl' => $this->publicProjectUrl($vehicle, $slugs),
            'settings' => $magazines->settingsFor($vehicle),
        ]);
    }

    public function updateQrSettings(Vehicle $vehicle, MagazineService $magazines): RedirectResponse
    {
        Gate::authorize('update', $vehicle);
        $magazines->settingsFor($vehicle)->update($magazines->validateQr(request()->all()));

        return back()->with('status', 'Identidade do QR Code salva.');
    }

    public function updateSlug(Vehicle $vehicle, VehicleSlugService $slugs): RedirectResponse
    {
        Gate::authorize('update', $vehicle);
        $data = request()->validate(['slug' => ['required', 'string', 'min:3', 'max:170', 'regex:/^[a-z][a-z0-9-]*$/']]);
        $slugs->change($vehicle, $data['slug']);

        return back()->with('status', 'URL pública atualizada. Links anteriores continuam funcionando.');
    }

    public function qrCode(Vehicle $vehicle, QrCodeService $qr, ShareEventLogger $events, MagazineService $magazines, VehicleSlugService $slugs): Response
    {
        Gate::authorize('update', $vehicle);
        $url = $this->publicProjectUrl($vehicle, $slugs);
        $settings = $magazines->settingsFor($vehicle);
        $events->log(request(), $vehicle, 'qr_view', 'owner');

        return response($qr->svg($url, $settings->qr_foreground, $settings->qr_background), 200, ['Content-Type' => 'image/svg+xml; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    public function download(Vehicle $vehicle, QrCodeService $qr, ShareEventLogger $events, MagazineService $magazines, VehicleSlugService $slugs): Response
    {
        Gate::authorize('update', $vehicle);
        $url = $this->publicProjectUrl($vehicle, $slugs);
        $format = request()->string('format', 'png')->lower()->toString();
        abort_unless(in_array($format, ['png', 'svg', 'sticker'], true), 422);
        $settings = $magazines->settingsFor($vehicle);
        $events->log(request(), $vehicle, 'qr_download', $format);

        if ($format === 'svg') {
            return response($qr->svg($url, $settings->qr_foreground, $settings->qr_background), 200, ['Content-Type' => 'image/svg+xml; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="autoverse-'.$vehicle->slug.'.svg"']);
        }
        if ($format === 'sticker') {
            return response($qr->sticker($url, $settings->qr_foreground, $settings->qr_background, $settings->qr_label ?: ($vehicle->public_name ?: $vehicle->model), $settings->qr_brand ?: 'Autoverse'), 200, ['Content-Type' => 'image/svg+xml; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="autoverse-'.$vehicle->slug.'-sticker.svg"']);
        }

        return response($qr->png($url, $settings->qr_foreground, $settings->qr_background), 200, ['Content-Type' => 'image/png', 'Content-Disposition' => 'attachment; filename="autoverse-'.$vehicle->slug.'.png"']);
    }

    public function publicQr(string $slug, QrCodeService $qr, ShareEventLogger $events, MagazineService $magazines, VehicleSlugService $slugs): Response|RedirectResponse
    {
        $vehicle = Vehicle::query()->where('slug', $slug)->first();
        if (! $vehicle) {
            $history = VehicleSlugHistory::query()->with('vehicle')->where('slug', $slug)->first();
            abort_unless($history?->vehicle && $history->vehicle->visibility === 'public', 404);

            return redirect()->route('vehicles.slug.qr', ['slug' => $history->vehicle->slug], 301);
        }
        abort_unless($vehicle->visibility === 'public', 404);
        $events->log(request(), $vehicle, 'qr_view', 'public');
        $url = $this->publicProjectUrl($vehicle, $slugs);
        $settings = $magazines->settingsFor($vehicle);

        return response($qr->svg($url, $settings->qr_foreground, $settings->qr_background), 200, ['Content-Type' => 'image/svg+xml; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600']);
    }

    private function publicProjectUrl(Vehicle $vehicle, VehicleSlugService $slugs): string
    {
        $project = $vehicle->projects()
            ->where('is_public', true)
            ->latest('id')
            ->first();

        return $project && $vehicle->magazineSetting?->is_published !== false
            ? route('projects.show', $project)
            : route('vehicles.public.show', ['key' => $slugs->ensurePublicKey($vehicle)]);
    }
}
