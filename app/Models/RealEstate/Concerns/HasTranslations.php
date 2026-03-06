<?php

namespace App\Models\RealEstate\Concerns;

trait HasTranslations
{
    public function translated(?string $locale = null)
    {
        $locale ??= app()->getLocale();
        $defaultLocale = config('locales.default', 'en');

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', $defaultLocale)
            ?? $this->translations->first();
    }
}
