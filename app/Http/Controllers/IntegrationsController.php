<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;
use ZipArchive;

/**
 * Página de integraciones: extensión de navegador, etc.
 */
class IntegrationsController extends Controller
{
    public function index(): View
    {
        $version = $this->getExtensionVersion();
        $versionDisplay = is_numeric(trim($version))
            ? \Carbon\Carbon::createFromTimestamp((int) $version)->isoFormat('D/M/YYYY H:mm')
            : $version;

        return view('integrations.index', [
            'extensionVersion' => $versionDisplay,
        ]);
    }

    /**
     * Obtiene la versión de la extensión desde version.txt (timestamp) o fallbacks.
     * Fuente única de verdad: browser-extension/version.txt con Unix timestamp.
     */
    private function getExtensionVersion(): string
    {
        $versionPath = base_path('browser-extension/version.txt');
        if (File::exists($versionPath)) {
            $version = trim(File::get($versionPath));
            if ($version !== '') {
                return $version;
            }
        }
        $manifestPath = base_path('browser-extension/manifest.json');
        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
            if (! empty($manifest['version'])) {
                return $manifest['version'];
            }
        }
        $fallbackPath = public_path('extension-assets/browser-extension.json');
        if (File::exists($fallbackPath)) {
            $json = json_decode(File::get($fallbackPath), true);
            if (! empty($json['version'])) {
                return $json['version'];
            }
        }
        return (string) time();
    }

    /**
     * API pública para que la extensión compruebe si hay una versión nueva (actualización desde tu página, sin Chrome Web Store).
     */
    public function extensionVersion(Request $request): JsonResponse
    {
        $version = $this->getExtensionVersion();
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        $downloadUrl = $baseUrl . '/integrations/extension/download';

        return response()->json([
            'version' => $version,
            'name' => 'PassVault',
            'download_url' => $downloadUrl,
            'integrations_url' => $baseUrl . '/integrations',
        ]);
    }

    /**
     * Descarga la extensión de navegador empaquetada en ZIP para instalar en Chrome (Cargar descomprimida).
     * La URL base del vault (local o producción) se inyecta según desde dónde se descargue.
     */
    public function downloadExtension(Request $request): Response
    {
        $sourceDir = base_path('browser-extension');
        if (! File::isDirectory($sourceDir)) {
            abort(404, 'Extensión no disponible.');
        }

        $vaultBaseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

        $zipPath = storage_path('app/passvault-extension-' . uniqid('', true) . '.zip');
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'No se pudo crear el archivo de descarga.');
        }

        $exclude = ['.git', '.DS_Store', '__MACOSX', 'node_modules'];
        $this->addDirectoryToZip($zip, $sourceDir, $sourceDir, $exclude, $vaultBaseUrl);
        $zip->close();

        $filename = 'passvault-extension.zip';

        return response()->download($zipPath, $filename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Añade recursivamente un directorio al ZIP, excluyendo nombres indicados.
     * Inyecta la URL base del vault en popup/popup.js cuando se genera el ZIP para descarga.
     */
    private function addDirectoryToZip(ZipArchive $zip, string $basePath, string $currentPath, array $exclude, string $vaultBaseUrl = ''): void
    {
        $directories = File::directories($currentPath);
        foreach ($directories as $dir) {
            $name = basename($dir);
            if (in_array($name, $exclude, true)) {
                continue;
            }
            $this->addDirectoryToZip($zip, $basePath, $dir, $exclude, $vaultBaseUrl);
        }

        $files = File::files($currentPath);
        foreach ($files as $file) {
            $path = $file->getPathname();
            $relative = substr($path, strlen($basePath) + 1);
            $relativeNormalized = str_replace('\\', '/', $relative);

            if ($relativeNormalized === 'popup/popup.js' && $vaultBaseUrl !== '') {
                $content = File::get($path);
                $content = str_replace('__VAULT_BASE_URL__', $vaultBaseUrl, $content);
                $zip->addFromString($relativeNormalized, $content);
            } else {
                $zip->addFile($path, $relativeNormalized);
            }
        }
    }
}
