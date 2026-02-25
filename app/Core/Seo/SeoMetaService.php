<?php

namespace App\Core\Seo;

use App\Models\Core\SeoMeta;
use Illuminate\Database\Eloquent\Model;

class SeoMetaService
{
    public function syncFor(Model $model, array $input): void
    {
        foreach (['en', 'fr', 'es'] as $locale) {
            $title = $input['meta_title_'.$locale] ?? null;
            $description = $input['meta_description_'.$locale] ?? null;
            $canonical = $input['canonical_url_'.$locale] ?? null;

            if (! $title && ! $description && ! $canonical) {
                continue;
            }

            SeoMeta::query()->updateOrCreate(
                [
                    'metaable_type' => $model::class,
                    'metaable_id' => $model->getKey(),
                    'locale' => $locale,
                ],
                [
                    'meta_title' => $title,
                    'meta_description' => $description,
                    'canonical_url' => $canonical,
                    'og_title' => $title,
                    'og_description' => $description,
                    'robots' => $input['robots_'.$locale] ?? 'index,follow',
                    'schema_json' => $this->toJson($input['schema_json_'.$locale] ?? null),
                ],
            );
        }
    }

    private function toJson(?string $value): ?array
    {
        if (! $value) {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
