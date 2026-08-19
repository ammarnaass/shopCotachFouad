<?php

namespace App\Data\Repositories\Eloquent;

use App\Data\Repositories\Contracts\SettingsRepositoryInterface;
use App\Models\Settings\Setting;

class EloquentSettingsRepository implements SettingsRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }

    public function set(string $key, mixed $value, string $group = 'general'): void
    {
        Setting::set($key, $value, $group);
    }

    public function getGroup(string $group): array
    {
        return Setting::where('group', $group)->pluck('value', 'key')->toArray();
    }

    public function setGroup(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::set($key, $value, $group);
        }
    }
}
