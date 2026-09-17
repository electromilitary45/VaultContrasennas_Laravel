<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Actualiza la versión de la extensión por timestamp y sincroniza el JSON público.
 *
 * Fuente única de verdad: browser-extension/version.txt (Unix timestamp).
 * Al ejecutar, escribe el timestamp actual en version.txt y en extension-assets/browser-extension.json.
 */
class SyncExtensionVersionCommand extends Command
{
    protected $signature = 'extension:sync-version';

    protected $description = 'Escribe timestamp actual en version.txt y sincroniza el JSON público de la extensión';

    public function handle(): int
    {
        $timestamp = (string) time();
        $versionPath = base_path('browser-extension/version.txt');
        $extensionDir = base_path('browser-extension');

        if (! File::isDirectory($extensionDir)) {
            $this->error('No se encuentra el directorio browser-extension.');

            return self::FAILURE;
        }

        File::put($versionPath, $timestamp . "\n");
        $this->info("Timestamp escrito: {$timestamp} → browser-extension/version.txt");

        $manifestPath = base_path('browser-extension/manifest.json');
        $name = 'PassVault';
        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
            if (! empty($manifest['name'])) {
                $name = $manifest['name'];
            }
        }

        $targetDir = public_path('extension-assets');
        $targetPath = $targetDir . '/browser-extension.json';
        $payload = [
            'version' => $timestamp,
            'name' => $name,
            'chrome_store_url' => '',
            'release_notes' => '',
        ];

        if (File::exists($targetPath)) {
            $existing = json_decode(File::get($targetPath), true);
            if (is_array($existing)) {
                $payload['chrome_store_url'] = $existing['chrome_store_url'] ?? '';
                $payload['release_notes'] = $existing['release_notes'] ?? '';
            }
        }

        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        File::put($targetPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("JSON público actualizado: version = {$timestamp}");

        return self::SUCCESS;
    }
}
