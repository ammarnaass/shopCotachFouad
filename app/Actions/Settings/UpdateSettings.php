<?php

namespace App\Actions\Settings;

use App\Services\EnvironmentService;
use App\Services\ImageUploadService;
use App\Services\SettingsService;
use Illuminate\Http\UploadedFile;

class UpdateSettings
{
    public function __construct(
        private SettingsService $settings,
        private EnvironmentService $environment,
        private ImageUploadService $images,
    ) {}

    public function execute(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            if (str_ends_with($key, '_file')) {
                continue;
            }
            if ($value === '' || $value === null) {
                $this->settings->set($key, $value, $group);

                continue;
            }
            $this->settings->set($key, $value, $group);
        }

        $this->settings->flush();
    }

    public function updateEnvironment(array $data): void
    {
        if (isset($data['store_country'])) {
            $this->environment->update('APP_DEFAULT_COUNTRY', $data['store_country']);
        }
        if (isset($data['fallback_currency'])) {
            $this->environment->update('APP_FALLBACK_CURRENCY', $data['fallback_currency']);
        }
    }

    public function handleFileUpload(string $key, ?UploadedFile $file, string $folder, int $maxKb = 512, string $mimes = 'jpg,jpeg,png,webp', ?string $oldPath = null): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        return $this->images->upload($file, $folder, $maxKb, $mimes, $oldPath);
    }
}
