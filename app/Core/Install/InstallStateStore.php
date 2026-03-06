<?php

namespace App\Core\Install;

use App\Models\Core\InstallState;
use Illuminate\Support\Facades\File;

class InstallStateStore
{
    private string $stateFile;

    public function __construct()
    {
        $this->stateFile = storage_path('app/install_state.json');
    }

    public function get(): array
    {
        $fileState = $this->getFileState();

        if ($this->canUseDatabase()) {
            $record = InstallState::query()->first();
            if ($record) {
                return [
                    'current_step' => $record->current_step,
                    'payload' => $record->payload ?? [],
                    'is_completed' => (bool) $record->is_completed,
                    'completed_at' => $record->completed_at,
                ];
            }
        }

        return $fileState;
    }

    public function setStep(int $step, array $payload = []): void
    {
        $state = $this->get();
        $newState = [
            'current_step' => $step,
            'payload' => array_merge($state['payload'] ?? [], $payload),
            'is_completed' => false,
            'completed_at' => null,
        ];

        $this->writeFileState($newState);

        if ($this->canUseDatabase()) {
            InstallState::query()->updateOrCreate(['id' => 1], $newState);
        }
    }

    public function complete(array $payload = []): void
    {
        $state = [
            'current_step' => 6,
            'payload' => array_merge($this->get()['payload'] ?? [], $payload),
            'is_completed' => true,
            'completed_at' => now()->toDateTimeString(),
        ];

        $this->writeFileState($state);

        if ($this->canUseDatabase()) {
            InstallState::query()->updateOrCreate(['id' => 1], $state);
        }
    }

    private function getFileState(): array
    {
        if (! File::exists($this->stateFile)) {
            return ['current_step' => 1, 'payload' => [], 'is_completed' => false, 'completed_at' => null];
        }

        $decoded = json_decode((string) File::get($this->stateFile), true);

        return is_array($decoded) ? $decoded : ['current_step' => 1, 'payload' => [], 'is_completed' => false, 'completed_at' => null];
    }

    private function writeFileState(array $state): void
    {
        File::ensureDirectoryExists(dirname($this->stateFile));
        File::put($this->stateFile, json_encode($state, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    private function canUseDatabase(): bool
    {
        try {
            return app('db')->connection()->getSchemaBuilder()->hasTable('install_state');
        } catch (\Throwable) {
            return false;
        }
    }
}
