<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicReferences
{
    public static function matching(string $type, Request $request): Collection
    {
        if ($type === 'events' && $request->boolean('mine') && $request->user()) {
            return collect();
        }
        $term = mb_strtolower(mb_substr((string) $request->string('q'), 0, 100));

        return collect(config('public_references.'.$type, []))->filter(function ($item) use ($type, $request, $term) {
            if ($type === 'events' && $item['ends_on'] < now('America/Sao_Paulo')->toDateString()) {
                return false;
            }
            if ($type === 'events' && $request->filled('state') && $item['state'] !== strtoupper((string) $request->string('state'))) {
                return false;
            }
            $haystack = $type === 'events' ? $item['city'] : implode(' ', [$item['title'], $item['city'], $item['description'], $item['search'] ?? '']);

            return $term === '' || str_contains(mb_strtolower($haystack), $term);
        })->values();
    }
}
