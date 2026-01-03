<?php

namespace App\Feature\Media\Provider;

use App\Entity\Media\ValueObject\FileSource;
use App\Entity\Media\ValueObject\StoredFileResult;
use App\Feature\Media\DTO\FileUploadRequest;
use App\Feature\Media\Exception\StorageException;
use Exception;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Bridge pour utiliser VichUploader comme provider
 * Utile pour une migration progressive depuis VichUploader vers Flysystem
 *
 * Installation: composer require vich/uploader-bundle
 */
class VichUploaderBridge implements FileStorageProvider
{
    public function __construct(
        private StorageInterface $vichStorage,
        private PropertyMappingFactory $mappingFactory,
        private string $mappingName = 'default_mapping',
        private string $publicUrlBase = ''
    ) {}

    public function store(FileUploadRequest $request): StoredFileResult
    {
        try {
            $request->validate();

            $sourcePath = $this->getSourcePath($request);
            $file = new File($sourcePath);

            // Création d'un objet temporaire pour VichUploader
            $uploadable = new class {
                public ?File $file = null;
                public ?string $fileName = null;
            };

            $uploadable->file = $file;

            // VichUploader gère automatiquement l'upload via les listeners
            // Mais ici on doit le faire manuellement car c'est un objet temporaire

            $mapping = $this->mappingFactory->fromField(
                $uploadable,
                'file',
                get_class($uploadable)
            );

            if (!$mapping) {
                throw new StorageException(
                    "VichUploader mapping '{$this->mappingName}' not found"
                );
            }

            // Génération du nom de fichier
            $originalName = $request->originalFilename ?? $file->getFilename();
            $safeName = $this->generateSafeFilename($originalName);

            // Définir le chemin de destination
            $folder = $request->options['customFolder'] ?? date('Y/m');
            $storagePath = $folder . '/' . $safeName;

            $uploadable->fileName = $storagePath;

            // Upload via VichUploader
            $this->vichStorage->upload($uploadable, new PropertyMapping('file', 'fileName'));

            // Métadonnées
            $mimeType = $file->getMimeType() ?? 'application/octet-stream';
            $size = $file->getSize();
            $metadata = $this->extractMetadata($sourcePath, $mimeType);

            // Nettoyage fichier temporaire si download
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

        } catch (Exception $e) {
            throw new StorageException(
                "VichUploader storage failed: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public function getPublicUrl(string $path): string
    {
        // Construction de l'URL publique
        // VichUploader génère typiquement des URLs comme /uploads/...
        if ($this->publicUrlBase) {
            return rtrim($this->publicUrlBase, '/') . '/' . ltrim($path, '/');
        }

        // Fallback
        return '/uploads/' . ltrim($path, '/');
    }

    public function delete(string $path): void
    {
        try {
            // Création d'un objet temporaire pour la suppression
            $uploadable = new class {
                public ?string $fileName = null;
            };

            $uploadable->fileName = $path;

            // Suppression via VichUploader
            $this->vichStorage->remove($uploadable, new PropertyMapping('file', 'fileName'));

        } catch (Exception $e) {
            throw new StorageException(
                "VichUploader delete failed: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public function exists(string $path): bool
    {
        // VichUploader n'a pas de méthode exists() native
        // On doit construire le chemin complet et vérifier
        try {
            $uploadable = new class {
                public ?string $fileName = null;
            };

            $uploadable->fileName = $path;

            $resolvedPath = $this->vichStorage->resolvePath($uploadable, 'file');

            return $resolvedPath && file_exists($resolvedPath);

        } catch (Exception $e) {
            return false;
        }
    }

    public function read(string $path)
    {
        try {
            $uploadable = new class {
                public ?string $fileName = null;
            };

            $uploadable->fileName = $path;

            $resolvedPath = $this->vichStorage->resolvePath($uploadable, 'file');

            if (!$resolvedPath || !file_exists($resolvedPath)) {
                throw new StorageException("File not found: {$path}");
            }

            return file_get_contents($resolvedPath);

        } catch (Exception $e) {
            throw new StorageException(
                "Failed to read file: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public function getName(): string
    {
        return 'vich';
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
        $tmpFile = tempnam(sys_get_temp_dir(), 'vich_upload_');

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
