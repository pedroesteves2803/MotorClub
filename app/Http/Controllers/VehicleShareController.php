<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleSlugHistory;
use App\Services\QrCodeService;
use App\Services\ShareEventLogger;
use App\Services\VehicleSlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class VehicleShareController extends Controller
{
    public function edit(Vehicle $vehicle): Response
    {
        Gate::authorize('update', $vehicle);

        $slug = app(VehicleSlugService::class)->ensure($vehicle);

        return response()->view('vehicles.share', [
            'vehicle' => $vehicle,
            'publicUrl' => route('vehicles.slug.show', ['slug' => $slug]),
        ]);
    }

    public function updateSlug(Vehicle $vehicle, VehicleSlugService $slugs): RedirectResponse
    {
        Gate::authorize('update', $vehicle);
        $data = request()->validate(['slug' => ['required', 'string', 'min:3', 'max:170', 'regex:/^[a-z][a-z0-9-]*$/']]);
        $slugs->change($vehicle, $data['slug']);

        return back()->with('status', 'URL pública atualizada. Links anteriores continuam funcionando.');
    }

    public function qrCode(Vehicle $vehicle, QrCodeService $qr, ShareEventLogger $events): Response
    {
        Gate::authorize('update', $vehicle);
        $url = route('vehicles.slug.show', ['slug' => app(VehicleSlugService::class)->ensure($vehicle)]);
        $events->log(request(), $vehicle, 'qr_view', 'owner');

        return response($qr->svg($url), 200, ['Content-Type' => 'image/svg+xml; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    public function download(Vehicle $vehicle, QrCodeService $qr, ShareEventLogger $events): Response
    {
        Gate::authorize('update', $vehicle);
        $url = route('vehicles.slug.show', ['slug' => app(VehicleSlugService::class)->ensure($vehicle)]);
        $format = request()->string('format', 'png')->lower()->toString();
        abort_unless(in_array($format, ['png', 'svg'], true), 422);
        $events->log(request(), $vehicle, 'qr_download', $format);

        if ($format === 'svg') {
            return response($qr->svg($url), 200, ['Content-Type' => 'image/svg+xml; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="autoverse-'.$vehicle->slug.'.svg"']);
        }

        return response($qr->png($url), 200, ['Content-Type' => 'image/png', 'Content-Disposition' => 'attachment; filename="autoverse-'.$vehicle->slug.'.png"']);
    }

    public function publicQr(string $slug, QrCodeService $qr, ShareEventLogger $events): Response|RedirectResponse
    {
        $vehicle = Vehicle::query()->where('slug', $slug)->first();
        if (! $vehicle) {
            $history = VehicleSlugHistory::query()->with('vehicle')->where('slug', $slug)->first();
            abort_unless($history?->vehicle && $history->vehicle->visibility === 'public', 404);

            return redirect()->route('vehicles.slug.qr', ['slug' => $history->vehicle->slug], 301);
        }
        abort_unless($vehicle->visibility === 'public', 404);
        $events->log(request(), $vehicle, 'qr_view', 'public');
        $url = route('vehicles.slug.show', ['slug' => $vehicle->slug]);

        return response($qr->svg($url), 200, ['Content-Type' => 'image/svg+xml; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600']);
    }
}
