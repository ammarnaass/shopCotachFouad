<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class EnvironmentService
{
    public function update(string $key, ?string $value): void
    {
        $path = base_path('.env');

        if (!File::exists($path)) {
            return;
        }

        $content = File::get($path);

        if (str_contains($content, "{$key}=")) {
            $content = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $content
            );
        } else {
            $content .= "\n{$key}={$value}\n";
        }

        File::put($path, $content);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return env($key, $default);
    }
}
