<?php

namespace App\Modules\MigrationTools\Services;

use App\Core\Audit\AuditLogger;
use App\Core\Lock\LockService;
use App\Models\CmsPages\Page;
use App\Models\Core\Redirect;
use App\Models\RealEstate\City;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyType;
use App\Models\RealEstate\RentalMode;
use App\Modules\Blog\Models\Post;
use App\Modules\MigrationTools\Models\MigrationItem;
use App\Modules\MigrationTools\Models\MigrationRun;
use Illuminate\Support\Facades\Log;

class MigrationManager
{
    public function __construct(
        private readonly LockService $lockService,
        private readonly AuditLogger $auditLogger,
        private readonly WxrImportService $wxrImportService,
        private readonly WpRestImportService $wpRestImportService,
        private readonly CsvImportService $csvImportService,
    ) {
    }

    public function processRun(MigrationRun $run): void
    {
        $profileId = $run->profile_id ?: 'none';
        $lock = $this->lockService->runWithLock('migration:run:global', 3600, function () use ($run, $profileId) {
            return $this->lockService->runWithLock('migration:profile:'.$profileId, 3600, function () use ($run) {
                $run->update(['status' => 'running', 'started_at' => now()]);
                $options = $run->options ?? [];
                $dryRun = (bool) ($options['dry_run'] ?? false);
                $strategy = $options['overwrite_strategy'] ?? 'skip_if_exists';
                $createRedirects = (bool) ($options['create_redirects'] ?? false);
                $locale = $options['locale'] ?? config('locales.default', 'en');

                $logger = Log::build(['driver' => 'single', 'path' => storage_path('logs/migration.log')]);
                $payload = $this->payloadForRun($run, $options);
                $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0, 'delta_skipped' => 0];

                foreach ($payload['pages'] ?? [] as $page) {
                    $this->importItem($run, 'page', $page['source_id'] ?: $page['slug'], $page['slug'], $page, $summary, function () use ($page, $dryRun, $strategy, $locale, $createRedirects) {
                        $existing = Page::query()->where('slug', $page['slug'])->first();
                        if ($existing && $strategy === 'skip_if_exists') {
                            return ['status' => 'skipped', 'target' => $existing];
                        }
                        if ($dryRun) {
                            return ['status' => $existing ? 'updated' : 'created'];
                        }

                        $model = $existing ?? new Page();
                        $model->fill(['slug' => $page['slug'], 'template' => 'default', 'status' => $page['status'], 'published_at' => now()]);
                        $model->save();
                        $model->translations()->updateOrCreate(['locale' => $locale], ['title' => strip_tags($page['title']), 'content' => $page['content']]);

                        if ($createRedirects && ! empty($page['link'])) {
                            $oldPath = parse_url($page['link'], PHP_URL_PATH) ?: '/';
                            Redirect::query()->updateOrCreate(['from_path' => $oldPath], ['to_url' => url('/'.$model->slug), 'status_code' => 301, 'is_active' => true]);
                        }

                        return ['status' => $existing ? 'updated' : 'created', 'target' => $model];
                    }, $logger);
                }

                foreach ($payload['posts'] ?? [] as $post) {
                    $this->importItem($run, 'post', $post['source_id'] ?: $post['slug'], $post['slug'], $post, $summary, function () use ($post, $dryRun, $strategy, $locale, $createRedirects) {
                        $existing = Post::query()->where('slug', $post['slug'])->first();
                        if ($existing && $strategy === 'skip_if_exists') {
                            return ['status' => 'skipped', 'target' => $existing];
                        }
                        if ($dryRun) {
                            return ['status' => $existing ? 'updated' : 'created'];
                        }

                        $model = $existing ?? new Post();
                        $model->fill(['slug' => $post['slug'], 'status' => $post['status'], 'published_at' => now()]);
                        $model->save();
                        $model->translations()->updateOrCreate(['locale' => $locale], ['title' => strip_tags($post['title']), 'content' => $post['content']]);

                        if ($createRedirects && ! empty($post['link'])) {
                            $oldPath = parse_url($post['link'], PHP_URL_PATH) ?: '/';
                            Redirect::query()->updateOrCreate(['from_path' => $oldPath], ['to_url' => url('/blog/'.$model->slug), 'status_code' => 301, 'is_active' => true]);
                        }

                        return ['status' => $existing ? 'updated' : 'created', 'target' => $model];
                    }, $logger);
                }

                foreach ($payload['properties'] ?? [] as $property) {
                    $this->importItem($run, 'property', $property['source_id'] ?: $property['slug'], $property['slug'], $property, $summary, function () use ($property, $dryRun, $strategy, $locale) {
                        $existing = Property::query()->where('slug', $property['slug'])->first();
                        if ($existing && $strategy === 'skip_if_exists') {
                            return ['status' => 'skipped', 'target' => $existing];
                        }
                        if ($dryRun) {
                            return ['status' => $existing ? 'updated' : 'created'];
                        }

                        $typeId = PropertyType::query()->value('id');
                        $modeId = RentalMode::query()->value('id');
                        $cityId = City::query()->value('id');

                        $model = $existing ?? new Property();
                        $model->fill([
                            'slug' => $property['slug'],
                            'status' => $property['status'] === 'published' ? 'published' : 'draft',
                            'property_type_id' => $typeId,
                            'rental_mode_id' => $modeId,
                            'city_id' => $cityId,
                            'base_price_per_night' => (float) ($property['base_price_per_night'] ?? 0),
                            'currency' => 'MAD',
                            'max_guests' => (int) ($property['max_guests'] ?? 1),
                            'bedrooms' => 1,
                            'beds' => 1,
                            'bathrooms' => 1,
                            'published_at' => now(),
                        ]);
                        $model->save();
                        $model->translations()->updateOrCreate(['locale' => $locale], ['title' => strip_tags($property['title']), 'description' => $property['description'] ?? null]);

                        return ['status' => $existing ? 'updated' : 'created', 'target' => $model];
                    }, $logger);
                }

                if ($run->profile) {
                    $run->profile->update(['last_successful_run_at' => now()]);
                }

                $run->update(['status' => 'completed', 'finished_at' => now(), 'summary' => $summary]);
            });
        });

        if ($lock === null) {
            $run->update(['status' => 'failed', 'finished_at' => now(), 'summary' => ['error' => 'Another migration/profile run is active']]);
        }
    }

    public function rollbackRun(MigrationRun $run): array
    {
        $deleted = 0;
        $skipped = 0;

        foreach ($run->items()->where('result', 'created')->whereNull('rolled_back_at')->get() as $item) {
            if ($this->rollbackItem($item)) {
                $deleted++;
            } else {
                $skipped++;
            }
        }

        $this->auditLogger->log('migration.run.rollback', $run, ['deleted' => $deleted, 'skipped' => $skipped]);

        return compact('deleted', 'skipped');
    }

    public function rollbackItem(MigrationItem $item): bool
    {
        if ($item->result !== 'created' || ! $item->target_type || ! $item->target_id || $item->rolled_back_at) {
            return false;
        }

        $modelClass = $item->target_type;
        if (! class_exists($modelClass)) {
            return false;
        }

        $model = $modelClass::query()->find($item->target_id);
        if (! $model) {
            $item->update(['rolled_back_at' => now()]);

            return true;
        }

        if ($item->run->finished_at && $model->updated_at && $model->updated_at->gt($item->run->finished_at)) {
            return false;
        }

        $model->delete();
        $item->update(['rolled_back_at' => now(), 'status' => 'rolled_back']);
        $this->auditLogger->log('migration.item.rollback', $item, ['target_type' => $item->target_type, 'target_id' => $item->target_id]);

        return true;
    }

    private function payloadForRun(MigrationRun $run, array $options): array
    {
        if (($options['delta_strategy'] ?? 'full') === 'since_last_run' && $run->profile?->last_successful_run_at) {
            $options['modified_since'] = $run->profile->last_successful_run_at->toIso8601String();
        }

        $payload = match ($options['source_type']) {
            'wp_rest' => $this->wpRestImportService->fetch((string) ($options['base_url'] ?? ''), $options),
            'csv' => $this->csvImportService->parse((string) ($options['csv_content'] ?? ''), $options['field_mapping'] ?? []),
            default => $this->wxrImportService->parse((string) ($options['wxr_content'] ?? '')),
        };

        if (($options['delta_strategy'] ?? 'full') === 'since_last_run') {
            foreach (['pages', 'posts', 'properties'] as $type) {
                $payload[$type] = collect($payload[$type] ?? [])->filter(function (array $item) use ($run, $type) {
                    $sourceId = (string) ($item['source_id'] ?: ($item['slug'] ?? ''));
                    $hash = hash('sha256', json_encode($item));

                    $previous = MigrationItem::query()
                        ->where('entity_type', rtrim($type, 's'))
                        ->where('source_id', $sourceId)
                        ->whereHas('run', fn ($q) => $q->where('profile_id', $run->profile_id)->where('status', 'completed'))
                        ->latest('id')
                        ->first();

                    return ! $previous || $previous->payload_hash !== $hash;
                })->values()->all();
            }
        }

        return $payload;
    }

    private function importItem(MigrationRun $run, string $type, string $sourceId, ?string $slug, array $payload, array &$summary, \Closure $callback, $logger): void
    {
        $item = MigrationItem::query()->updateOrCreate(
            ['run_id' => $run->id, 'entity_type' => $type, 'source_id' => $sourceId ?: $slug],
            ['source_slug' => $slug, 'payload_hash' => hash('sha256', json_encode($payload)), 'status' => 'running', 'result' => null, 'error_excerpt' => null],
        );

        try {
            $result = $callback();
            $status = $result['status'] ?? 'created';
            $item->update([
                'status' => $status,
                'result' => $status,
                'target_type' => isset($result['target']) ? get_class($result['target']) : null,
                'target_id' => $result['target']->id ?? null,
            ]);
            $summary[$status] = ($summary[$status] ?? 0) + 1;
        } catch (\Throwable $exception) {
            $item->update(['status' => 'failed', 'result' => 'failed', 'error_excerpt' => (string) str($exception->getMessage())->limit(500)]);
            $summary['errors']++;
            $logger->error('Migration item failed', ['run_id' => $run->id, 'type' => $type, 'source_id' => $sourceId, 'error' => $exception->getMessage()]);
        }
    }
}
