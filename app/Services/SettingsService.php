<?php

namespace App\Services;

use App\Data\Repositories\Contracts\SettingsRepositoryInterface;

class SettingsService
{
    public function __construct(
        private SettingsRepositoryInterface $settings,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings->get($key, $default);
    }

    public function set(string $key, mixed $value, string $group = 'general'): void
    {
        $this->settings->set($key, $value, $group);
    }

    public function getGroup(string $group): array
    {
        return $this->settings->getGroup($group);
    }

    public function setGroup(string $group, array $data): void
    {
        $this->settings->setGroup($group, $data);
    }

    public function flush(): void
    {
        \Illuminate\Support\Facades\Cache::forget('site_settings');
    }
}
