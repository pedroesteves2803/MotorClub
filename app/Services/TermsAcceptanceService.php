<?php

namespace App\Services;

use App\Models\TermsAcceptance;
use App\Models\User;
use Illuminate\Http\Request;

class TermsAcceptanceService
{
    public function hasCurrentAcceptance(User $user): bool
    {
        return TermsAcceptance::query()
            ->where('user_id', $user->id)
            ->whereIn('document_type', ['terms', 'community'])
            ->whereIn('version', [config('legal.terms_version'), config('legal.community_version')])
            ->distinct('document_type')
            ->count('document_type') === 2;
    }

    public function record(User $user, Request $request): void
    {
        foreach (['terms' => config('legal.terms_version'), 'community' => config('legal.community_version')] as $type => $version) {
            TermsAcceptance::updateOrCreate(
                ['user_id' => $user->id, 'document_type' => $type, 'version' => $version],
                [
                    'accepted_at' => now(),
                    'ip_hash' => $this->hash($request->ip()),
                    'user_agent_hash' => $this->hash((string) $request->userAgent()),
                ],
            );
        }
    }

    private function hash(?string $value): ?string
    {
        return filled($value) ? hash_hmac('sha256', $value, (string) config('app.key')) : null;
    }
}
