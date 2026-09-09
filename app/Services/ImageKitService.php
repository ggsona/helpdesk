<?php

namespace App\Services;

use ImageKit\ImageKit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para manejar la subida y descarga de archivos usando ImageKit.io
 * como almacenamiento externo (necesario en entornos como Render.com donde
 * el sistema de archivos es efímero).
 *
 * Si las variables de ImageKit no están configuradas, hace fallback al
 * disco 'public' local (útil para desarrollo).
 */
class ImageKitService
{
    protected ?ImageKit $imageKit = null;
    protected bool $enabled = false;

    public function __construct()
    {
        $publicKey  = config('imagekit.public_key');
        $privateKey = config('imagekit.private_key');
        $urlEndpoint = config('imagekit.url_endpoint');

        if ($publicKey && $privateKey && $urlEndpoint) {
            $this->imageKit = new ImageKit($publicKey, $privateKey, $urlEndpoint);
            $this->enabled  = true;
        }
    }

    /**
     * Sube un archivo a ImageKit o al disco local si ImageKit no está configurado.
     *
     * @param  UploadedFile  $file
     * @param  string        $folder  Carpeta destino en ImageKit (ej: 'tickets/123')
     * @return string        Ruta/URL del archivo almacenado
     */
    public function upload(UploadedFile $file, string $folder = 'uploads'): string
    {
        if (!$this->enabled) {
            // Fallback: disco local (desarrollo)
            return $file->store($folder, 'public');
        }

        try {
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());

            $result = $this->imageKit->uploadFile([
                'file'              => base64_encode(file_get_contents($file->getRealPath())),
                'fileName'          => $fileName,
                'folder'            => '/' . ltrim($folder, '/'),
                'useUniqueFileName' => false,
            ]);

            if (isset($result->result->url)) {
                return $result->result->url;
            }

            // Si falla la subida, log y fallback local
            Log::error('ImageKit upload failed', ['error' => $result->error ?? 'unknown']);
            return $file->store($folder, 'public');

        } catch (\Exception $e) {
            Log::error('ImageKit exception: ' . $e->getMessage());
            return $file->store($folder, 'public');
        }
    }

    /**
     * Elimina un archivo de ImageKit por su fileId.
     * Si ImageKit no está activo, no hace nada.
     *
     * @param  string $fileId  El fileId devuelto por ImageKit al subir
     */
    public function delete(string $fileId): void
    {
        if (!$this->enabled || !$this->imageKit) {
            return;
        }

        try {
            $this->imageKit->deleteFile($fileId);
        } catch (\Exception $e) {
            Log::error('ImageKit delete exception: ' . $e->getMessage());
        }
    }

    /**
     * Genera la URL pública de un archivo en ImageKit.
     *
     * @param  string $path  Ruta relativa en ImageKit (ej: '/tickets/123/foto.png')
     * @return string        URL completa del archivo
     */
    public function url(string $path): string
    {
        if (!$this->enabled || !$this->imageKit) {
            return asset('storage/' . $path);
        }

        return $this->imageKit->getUrl(['path' => $path]);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
