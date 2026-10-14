<?php

namespace App\Http\Controllers;

use App\Models\CookieConsent;
use App\Models\LegalDocument;
use App\Services\LegalContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LegalController extends Controller
{
    public function show(string $slug, LegalContentSanitizer $sanitizer)
    {
        abort_unless(array_key_exists($slug, $this->documents()), 404);
        $document = LegalDocument::query()->where('slug', $slug)->where('is_published', true)->latest('published_at')->first();
        $fallback = $this->documents()[$slug];
        $updatedAt = $document?->published_at ?: config('legal.updated_at');

        return view('legal.show', [
            'slug' => $slug,
            'title' => $document?->title ?: $fallback['title'],
            'version' => $document?->version ?: $fallback['version'],
            'updatedAt' => Carbon::parse($updatedAt),
            'content' => $sanitizer->sanitize($document?->content ?: $fallback['content']),
        ]);
    }

    public function help()
    {
        return view('legal.help');
    }

    public function cookieSettings(Request $request)
    {
        $visitorId = $request->cookie('autoverse_cookie_consent_id');
        $consent = CookieConsent::query()
            ->when($request->user(), fn ($query) => $query->where('user_id', $request->user()->id))
            ->when(! $request->user() && $visitorId, fn ($query) => $query->where('visitor_id', $visitorId))
            ->latest('consented_at')->first();

        return view('legal.cookies-settings', compact('consent'));
    }

    public function saveCookieSettings(Request $request): RedirectResponse
    {
        $data = $request->validate(['analytics' => ['sometimes', 'boolean'], 'personalization' => ['sometimes', 'boolean']]);
        $visitorId = $request->cookie('autoverse_cookie_consent_id') ?: (string) Str::uuid();
        CookieConsent::create([
            'user_id' => $request->user()?->id,
            'visitor_id' => $request->user() ? null : $visitorId,
            'analytics' => (bool) ($data['analytics'] ?? false),
            'personalization' => (bool) ($data['personalization'] ?? false),
            'policy_version' => config('legal.cookies_version'),
            'consented_at' => now(),
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
            'user_agent_hash' => hash_hmac('sha256', (string) $request->userAgent(), (string) config('app.key')),
        ]);

        return back()->withCookie(cookie(
            'autoverse_cookie_consent_id',
            $visitorId,
            60 * 24 * 365,
            '/',
            null,
            app()->environment('production') || $request->isSecure(),
            true,
            false,
            'lax',
        ))->with('status', 'Preferências de cookies salvas.');
    }

    private function documents(): array
    {
        return [
            'termos' => ['title' => 'Termos de Uso', 'version' => config('legal.terms_version'), 'content' => view('legal.documents.terms')->render()],
            'privacidade' => ['title' => 'Política de Privacidade', 'version' => config('legal.privacy_version'), 'content' => view('legal.documents.privacy')->render()],
            'cookies' => ['title' => 'Política de Cookies', 'version' => config('legal.cookies_version'), 'content' => view('legal.documents.cookies')->render()],
            'comunidade' => ['title' => 'Regulamento da Comunidade', 'version' => config('legal.community_version'), 'content' => view('legal.documents.community')->render()],
        ];
    }
}
