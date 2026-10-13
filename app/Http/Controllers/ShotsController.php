<?php

namespace App\Http\Controllers;

use App\Models\CarEvent;
use App\Models\PhotographerProfile;
use App\Models\ShotGallery;
use App\Models\ShotPhoto;
use App\Models\ShotPurchaseRequest;
use App\Models\ShotReport;
use App\Models\Vehicle;
use App\Services\ShotImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ShotsController extends Controller
{
    public function index(Request $request)
    {
        $query = ShotGallery::with(['user.photographerProfile', 'photos', 'event'])
            ->where('is_public', true)
            ->whereHas('user.photographerProfile', fn ($profile) => $profile->where('is_public', true));
        if ($request->filled('q')) {
            $term = mb_substr($request->string('q'), 0, 100);
            $query->where(fn ($gallery) => $gallery->where('title', 'like', "%{$term}%")->orWhereHas('user.photographerProfile', fn ($profile) => $profile->where('professional_name', 'like', "%{$term}%"))->orWhereHas('photos', fn ($photo) => $photo->where('brand', 'like', "%{$term}%")->orWhere('model', 'like', "%{$term}%")->orWhere('color', 'like', "%{$term}%")));
        }
        foreach (['city', 'state'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $field === 'state' ? $request->string($field)->upper() : $request->string($field));
            }
        }
        if ($request->filled('event')) {
            $query->where('car_event_id', $request->integer('event'));
        }
        if ($request->filled('date')) {
            $query->whereDate('shot_on', $request->date('date'));
        }

        return view('shots.index', ['galleries' => $query->latest('shot_on')->paginate(12)->withQueryString(), 'events' => CarEvent::where('is_public', true)->orderBy('starts_at')->get()]);
    }

    public function profile(Request $request)
    {
        $profile = PhotographerProfile::firstOrNew(['user_id' => $request->user()->id]);

        return view('shots.profile', compact('profile'));
    }

    public function saveProfile(Request $request)
    {
        $data = $request->validate(['professional_name' => 'required|string|max:120', 'description' => 'required|string|max:5000', 'city' => 'required|string|max:100', 'state' => ['required', Rule::in(config('motorclub.states'))], 'instagram' => 'nullable|string|max:100', 'website' => 'nullable|url|max:255', 'is_public' => 'required|boolean']);
        PhotographerProfile::updateOrCreate(['user_id' => $request->user()->id], $data);

        return back()->with('status', 'Perfil de fotógrafo salvo.');
    }

    public function myGalleries(Request $request)
    {
        return view('shots.manage', ['galleries' => $request->user()->shotGalleries()->with('photos')->latest()->get()]);
    }

    public function createGallery(Request $request)
    {
        $this->ensurePhotographer($request);

        return $this->galleryForm($request, new ShotGallery);
    }

    public function editGallery(Request $request, ShotGallery $gallery)
    {
        $this->ownsGallery($request, $gallery);

        return $this->galleryForm($request, $gallery);
    }

    private function galleryForm(Request $request, ShotGallery $gallery)
    {
        return view('shots.gallery-form', ['gallery' => $gallery, 'events' => CarEvent::where('is_public', true)->orWhere('user_id', $request->user()->id)->orderByDesc('starts_at')->get()]);
    }

    public function storeGallery(Request $request)
    {
        $this->ensurePhotographer($request);
        $gallery = ShotGallery::create($this->galleryData($request) + ['user_id' => $request->user()->id]);

        return redirect()->route('shots.galleries.edit', $gallery)->with('status', 'Galeria criada. Adicione suas fotos.');
    }

    public function updateGallery(Request $request, ShotGallery $gallery)
    {
        $this->ownsGallery($request, $gallery);
        $gallery->update($this->galleryData($request));

        return back()->with('status', 'Galeria atualizada.');
    }

    public function destroyGallery(Request $request, ShotGallery $gallery)
    {
        $this->ownsGallery($request, $gallery);
        $gallery->delete();

        return redirect()->route('shots.manage')->with('status', 'Galeria excluída.');
    }

    public function show(Request $request, ShotGallery $gallery)
    {
        abort_unless($gallery->is_public || $gallery->user_id === $request->user()?->id, 404);
        abort_unless($gallery->user->photographerProfile?->is_public || $gallery->user_id === $request->user()?->id, 404);
        $gallery->load([
            'user.photographerProfile',
            'event' => fn ($event) => $event->where('is_public', true),
            'photos' => fn ($photos) => $photos->where('is_published', true),
        ]);

        return view('shots.show', ['gallery' => $gallery, 'vehicles' => $request->user() ? Vehicle::where('user_id', $request->user()->id)->get() : collect()]);
    }

    public function uploadPhoto(Request $request, ShotGallery $gallery, ShotImages $images)
    {
        $this->ownsGallery($request, $gallery);
        $data = $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240|dimensions:max_width=6000,max_height=6000', 'brand' => 'nullable|string|max:80', 'model' => 'nullable|string|max:80', 'color' => 'nullable|string|max:50', 'price' => 'nullable|numeric|min:0|max:9999999999.99|decimal:0,2', 'license' => 'required|string|max:120', 'is_published' => 'required|boolean']);
        $uuid = (string) Str::uuid();
        try {
            $paths = $images->store($request->file('photo'), $uuid, $gallery->user->photographerProfile?->professional_name);
            $gallery->photos()->create($data + $paths + ['uuid' => $uuid, 'processing_status' => 'ready']);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['photo' => 'Não foi possível processar a foto. Tente novamente.']);
        }

        return back()->with('status', 'Foto adicionada com prévia protegida por marca-d’água.');
    }

    public function deletePhoto(Request $request, ShotPhoto $photo)
    {
        $this->ownsGallery($request, $photo->gallery);
        $photo->delete();

        return back()->with('status', 'Foto excluída.');
    }

    public function replacePhoto(Request $request, ShotPhoto $photo, ShotImages $images)
    {
        $this->ownsGallery($request, $photo->gallery);
        $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240|dimensions:max_width=6000,max_height=6000']);
        try {
            $paths = $images->store($request->file('photo'), $photo->uuid, $photo->gallery->user->photographerProfile?->professional_name);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['photo' => 'Não foi possível processar a substituição.']);
        }
        $oldPaths = [$photo->original_path, $photo->preview_path];
        $photo->update($paths + ['processing_status' => 'ready', 'processing_error' => null]);
        Storage::disk('local')->delete($oldPaths);

        return back()->with('status', 'Foto substituída e prévia protegida refeita.');
    }

    public function preview(Request $request, ShotPhoto $photo)
    {
        $owner = $photo->gallery->user_id === $request->user()?->id;
        abort_unless($owner || $photo->publiclyVisible(), 404);

        return Storage::disk('local')->response($photo->preview_path, null, ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function requestPurchase(Request $request, ShotPhoto $photo)
    {
        abort_unless($photo->publiclyVisible(), 404);
        abort_if($photo->gallery->user_id === $request->user()->id, 422, 'Você não pode solicitar sua própria foto.');
        $data = $request->validate(['vehicle_id' => ['nullable', Rule::exists('vehicles', 'id')->where('user_id', $request->user()->id)], 'message' => 'nullable|string|max:1000']);
        ShotPurchaseRequest::firstOrCreate(['shot_photo_id' => $photo->id, 'buyer_id' => $request->user()->id], $data + ['status' => 'pending']);

        return back()->with('status', 'Interesse registrado. O fotógrafo poderá responder pelo MotorClub.');
    }

    public function report(Request $request, ShotPhoto $photo)
    {
        abort_unless($photo->publiclyVisible(), 404);
        abort_if($photo->gallery->user_id === $request->user()->id, 422, 'Use a gestão da galeria para retirar sua própria foto.');
        $data = $request->validate(['reason' => 'required|string|max:120', 'details' => 'nullable|string|max:2000']);
        ShotReport::updateOrCreate(['shot_photo_id' => $photo->id, 'user_id' => $request->user()->id], $data + ['status' => 'open']);

        return back()->with('status', 'Solicitação registrada para análise.');
    }

    public function requests(Request $request)
    {
        $requests = ShotPurchaseRequest::with(['photo.gallery', 'buyer', 'vehicle'])->where(fn ($query) => $query->where('buyer_id', $request->user()->id)->orWhereHas('photo.gallery', fn ($gallery) => $gallery->where('user_id', $request->user()->id)))->latest()->paginate(20);

        return view('shots.requests', compact('requests'));
    }

    public function updateRequest(Request $request, ShotPurchaseRequest $purchase)
    {
        abort_unless($purchase->photo->gallery->user_id === $request->user()->id, 403);
        $data = $request->validate(['status' => ['required', Rule::in(['approved', 'declined'])], 'quoted_price' => 'nullable|numeric|min:0|max:9999999999.99|decimal:0,2']);
        $price = $data['quoted_price'] ?? $purchase->photo->price;
        if ($data['status'] === 'approved' && $price === null) {
            return back()->withErrors(['quoted_price' => 'Informe o preço ou defina a foto como gratuita.']);
        }
        $commission = config('motorclub.shots_platform_commission') / 100;
        DB::transaction(function () use ($purchase, $request, $data, $price, $commission) {
            $purchase = ShotPurchaseRequest::lockForUpdate()->findOrFail($purchase->id);
            abort_unless($purchase->photo->gallery->user_id === $request->user()->id, 403);
            abort_unless($purchase->status === 'pending' && $purchase->decided_at === null, 422, 'Esta solicitação já foi decidida.');
            $purchase->update($data + ['authorized_at' => $data['status'] === 'approved' ? now() : null, 'platform_amount' => $price === null ? 0 : round($price * $commission, 2), 'photographer_amount' => $price === null ? 0 : round($price * (1 - $commission), 2)]);
        });

        return back()->with('status', 'Solicitação atualizada. Nenhum pagamento ou download foi liberado.');
    }

    private function galleryData(Request $request): array
    {
        return $request->validate(['title' => 'required|string|max:150', 'description' => 'required|string|max:5000', 'city' => 'required|string|max:100', 'state' => ['required', Rule::in(config('motorclub.states'))], 'shot_on' => 'required|date|before_or_equal:today', 'car_event_id' => ['nullable', Rule::exists('car_events', 'id')->where(fn ($query) => $query->where('is_public', true)->orWhere('user_id', $request->user()->id))], 'is_public' => 'required|boolean']);
    }

    private function ensurePhotographer(Request $request): void
    {
        abort_unless($request->user()->photographerProfile()->exists(), 422, 'Ative seu perfil de fotógrafo antes de criar galerias.');
    }

    private function ownsGallery(Request $request, ShotGallery $gallery): void
    {
        abort_unless($gallery->user_id === $request->user()->id, 403);
    }
}
