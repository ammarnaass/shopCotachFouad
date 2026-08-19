<?php

namespace App\Services;

use App\Models\Content\FooterSection;
use App\Models\Content\FooterSocial;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FooterService
{
    private const SECTIONS_CACHE_KEY = 'footer_sections';
    private const SOCIALS_CACHE_KEY  = 'footer_socials';
    private const CACHE_TTL          = 600; // 10 minutes

    public function getSections(): Collection
    {
        return Cache::remember(self::SECTIONS_CACHE_KEY, self::CACHE_TTL, function () {
            return FooterSection::with(['links' => fn ($q) => $q->active()->orderBy('sort_order')])
                ->active()
                ->ordered()
                ->get();
        });
    }

    public function getSocials(): Collection
    {
        return Cache::remember(self::SOCIALS_CACHE_KEY, self::CACHE_TTL, function () {
            return FooterSocial::active()->ordered()->get();
        });
    }

    public function flush(): void
    {
        Cache::forget(self::SECTIONS_CACHE_KEY);
        Cache::forget(self::SOCIALS_CACHE_KEY);
    }
}
