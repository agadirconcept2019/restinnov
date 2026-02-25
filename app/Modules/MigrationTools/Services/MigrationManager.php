<?php

namespace App\Modules\MigrationTools\Services;

use App\Core\Lock\LockService;
use App\Models\CmsPages\Page;
use App\Models\Core\Redirect;
use App\Modules\Blog\Models\Post;
use App\Modules\MigrationTools\Models\MigrationItem;
use App\Modules\MigrationTools\Models\MigrationRun;
use Illuminate\Support\Facades\Log;

class MigrationManager
{
    public function __construct(
        private readonly LockService $lockService,
        private readonly WxrImportService $wxrImportService,
        private readonly WpRestImportService $wpRestImportService,
        private readonly CsvImportService $csvImportService,
    ) {
    }

    public function processRun(MigrationRun $run): void
    {
        $this->lockService->runWithLock('migration:run:global', 1800, function () use ($run) {
            $run->update(['status' => 'running', 'started_at' => now()]);
            $options = $run->options ?? [];
            $dryRun = (bool) ($options['dry_run'] ?? false);
            $strategy = $options['overwrite_strategy'] ?? 'skip_if_exists';
            $createRedirects = (bool) ($options['create_redirects'] ?? false);
            $locale = $options['locale'] ?? config('locales.default', 'en');

            $logger = Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/migration.log'),
            ]);

            $payload = $this->payloadForRun($options);
            $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];

            foreach ($payload['pages'] ?? [] as $page) {
                $this->importItem($run, 'page', $page['source_id'], $page['slug'], $page, $summary, function () use ($page, $dryRun, $strategy, $locale, $createRedirects) {
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
                $this->importItem($run, 'post', $post['source_id'], $post['slug'], $post, $summary, function () use ($post, $dryRun, $strategy, $locale, $createRedirects) {
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

            $run->update(['status' => 'completed', 'finished_at' => now(), 'summary' => $summary]);
        }) ?: $run->update(['status' => 'failed', 'finished_at' => now(), 'summary' => ['error' => 'Another migration run is active']]);
    }

    private function payloadForRun(array $options): array
    {
        return match ($options['source_type']) {
            'wp_rest' => $this->wpRestImportService->fetch((string) ($options['base_url'] ?? ''), $options),
            'csv' => $this->csvImportService->parse((string) ($options['csv_content'] ?? '')),
            default => $this->wxrImportService->parse((string) ($options['wxr_content'] ?? '')),
        };
    }

    private function importItem(MigrationRun $run, string $type, string $sourceId, ?string $slug, array $payload, array &$summary, \Closure $callback, $logger): void
    {
        $item = MigrationItem::query()->updateOrCreate(
            ['run_id' => $run->id, 'entity_type' => $type, 'source_id' => $sourceId ?: $slug],
            ['source_slug' => $slug, 'payload_hash' => hash('sha256', json_encode($payload)), 'status' => 'running', 'error_excerpt' => null],
        );

        try {
            $result = $callback();
            $status = $result['status'] ?? 'created';
            $item->update(['status' => $status, 'target_type' => isset($result['target']) ? get_class($result['target']) : null, 'target_id' => $result['target']->id ?? null]);
            $summary[$status] = ($summary[$status] ?? 0) + 1;
        } catch (\Throwable $exception) {
            $item->update(['status' => 'failed', 'error_excerpt' => (string) str($exception->getMessage())->limit(500)]);
            $summary['errors']++;
            $logger->error('Migration item failed', ['run_id' => $run->id, 'type' => $type, 'source_id' => $sourceId, 'error' => $exception->getMessage()]);
        }
    }
}
