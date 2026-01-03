<?php

namespace App\Feature\Media\Provider;

use App\Entity\Media\ValueObject\StoredFileResult;
use App\Feature\Media\DTO\FileUploadRequest;
use App\Feature\Media\Exception\StorageException;

/**
 * Interface de domaine - agnostique de l'implémentation
 * Les implémentations peuvent utiliser Flysystem, VichUploader, ou autre
 */
interface FileStorageProvider
{
    /**
     * Stocke un fichier et retourne les informations de stockage
     *
     * @throws StorageException
     */
    public function store(FileUploadRequest $request): StoredFileResult;

    /**
     * Récupère l'URL publique d'un fichier
     */
    public function getPublicUrl(string $path): string;

    /**
     * Supprime un fichier du stockage
     *
     * @throws StorageException
     */
    public function delete(string $path): void;

    /**
     * Vérifie si un fichier existe
     */
    public function exists(string $path): bool;

    /**
     * Retourne le nom du provider
     */
    public function getName(): string;

    /**
     * Télécharge le contenu d'un fichier
     *
     * @return resource|string
     * @throws StorageException
     */
    public function read(string $path);
}
