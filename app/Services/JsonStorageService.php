<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class JsonStorageService
{
    public function getPath(string $file): string
    {
        return storage_path("app/data/{$file}");
    }

    public function read(string $file): array
    {
        $path = $this->getPath($file);

        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        return json_decode($content, true) ?? [];
    }

    public function write(string $file, array $data): bool
    {
        $path = $this->getPath($file);
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($path, $json) !== false;
    }

    public function getNextId(string $file): int
    {
        $data = $this->read($file);

        if (empty($data)) {
            return 1;
        }

        return max(array_column($data, 'id')) + 1;
    }
}
