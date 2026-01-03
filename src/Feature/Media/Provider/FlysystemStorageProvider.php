<?php

namespace App\Feature\Media\Provider;

use App\Entity\Media\ValueObject\FileSource;
use App\Entity\Media\ValueObject\StoredFileResult;
use App\Feature\Media\DTO\FileUploadRequest;
use App\Feature\Media\Exception\StorageException;
use Exception;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;

/**
 * Provider générique utilisant Flysystem
 * Fonctionne avec n'importe quel adapter Flysystem (Local, S3, SFTP, etc.)
 */
class FlysystemStorageProvider implements FileStorageProvider
{
    public function __construct(
        private FilesystemOperator $filesystem,
        private string $name,
        private ?string $publicUrlBase = null
    ) {}

    public function store(FileUploadRequest $request): StoredFileResult
    {
        try {
            $request->validate();

            // Récupération du fichier source
            $sourcePath = $this->getSourcePath($request);
            $stream = fopen($sourcePath, 'r');

            if ($stream === false) {
                throw new StorageException("Cannot open source file: {$sourcePath}");
            }

            // Génération du chemin de destination
            $filename = $request->originalFilename ?? basename($sourcePath);
            $safeName = $this->generateSafeFilename($filename);
            $folder = $request->options['customFolder'] ?? date('Y/m');
            $storagePath = $folder . '/' . $safeName;

            // ✨ Utilisation de Flysystem pour l'upload
            $this->filesystem->writeStream($storagePath, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }

            // Récupération des métadonnées via Flysystem
            $mimeType = $this->filesystem->mimeType($storagePath);
            $size = $this->filesystem->fileSize($storagePath);

            // Extraction de métadonnées supplémentaires
            $metadata = $this->extractMetadata($sourcePath, $mimeType);

            // Nettoyage du fichier temporaire si téléchargé
            if ($request->source === FileSource::REMOTE_URL) {
                @unlink($sourcePath);
            }

            return new StoredFileResult(
                filename: $safeName,
                mimeType: $mimeType,
                size: $size,
                storagePath: $storagePath,
                metadata: $metadata
            );

        } catch (FilesystemException $e) {
            throw new StorageException(
                "Flysystem error: " . $e->getMessage(),
                0,
                $e
            );
        } catch (Exception $e) {
            throw new StorageException(
                "Storage failed: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public function getPublicUrl(string $path): string
    {
        // Si l'adapter supporte publicUrl() directement (ex: S3)
        try {
            if (method_exists($this->filesystem, 'publicUrl')) {
                return $this->filesystem->publicUrl($path);
            }
        } catch (Exception $e) {
            // Fallback sur l'URL de base
        }

        // Sinon, construction manuelle
        if ($this->publicUrlBase) {
            return rtrim($this->publicUrlBase, '/') . '/' . ltrim($path, '/');
        }

        throw new StorageException(
            "No public URL configured for storage '{$this->name}'"
        );
    }

    public function delete(string $path): void
    {
        try {
            if ($this->filesystem->fileExists($path)) {
                $this->filesystem->delete($path);
            }
        } catch (FilesystemException $e) {
            throw new StorageException(
                "Failed to delete file: {$path}",
                0,
                $e
            );
        }
    }

    public function exists(string $path): bool
    {
        try {
            return $this->filesystem->fileExists($path);
        } catch (FilesystemException $e) {
            return false;
        }
    }

    public function read(string $path)
    {
        try {
            return $this->filesystem->read($path);
        } catch (FilesystemException $e) {
            throw new StorageException(
                "Failed to read file: {$path}",
                0,
                $e
            );
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function getSourcePath(FileUploadRequest $request): string
    {
        if ($request->localPath) {
            return $request->localPath;
        }

        if ($request->remoteUrl) {
            return $this->downloadRemoteFile($request->remoteUrl);
        }

        throw new StorageException("No valid source provided");
    }

    private function downloadRemoteFile(string $url): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'upload_');

        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'FileManager/1.0'
            ]
        ]);

        if (!@copy($url, $tmpFile, $context)) {
            throw new StorageException("Failed to download remote file: {$url}");
        }

        return $tmpFile;
    }

    private function generateSafeFilename(string $filename): string
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

        return uniqid($safeName . '_', true) . '.' . $ext;
    }

    private function extractMetadata(string $path, string $mimeType): array
    {
        $metadata = [];

        if (str_starts_with($mimeType, 'image/')) {
            $imageInfo = @getimagesize($path);
            if ($imageInfo) {
                $metadata['width'] = $imageInfo[0];
                $metadata['height'] = $imageInfo[1];
            }
        }

        return $metadata;
    }
}
