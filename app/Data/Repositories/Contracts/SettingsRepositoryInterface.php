<?php

namespace App\Data\Repositories\Contracts;

interface SettingsRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, string $group = 'general'): void;

    public function getGroup(string $group): array;

    public function setGroup(string $group, array $data): void;
}
