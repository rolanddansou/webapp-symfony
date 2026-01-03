<?php

namespace App\Feature\Media\Service;

use App\Entity\Media\FileObject;
use App\Feature\Media\DTO\FileUploadRequest;
use App\Feature\Media\Event\FileDeletedEvent;
use App\Feature\Media\Event\FileUploadedEvent;
use App\Feature\Media\Exception\StorageException;
use App\Feature\Media\Provider\FileStorageProvider;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FileManager implements FileManagerInterface
{
    /** @var array<string, FileStorageProvider> */
    private array $providers = [];

    public function __construct(
        private readonly EntityManagerInterface   $em,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack  $requestStack,
        iterable                                  $providers
    ) {
        foreach ($providers as $provider) {
            $this->providers[$provider->getName()] = $provider;
        }
    }

    public function upload(
        FileUploadRequest $request,
        string $driverName = 'local'
    ): FileObject {
        $provider = $this->getProvider($driverName);

        $request->validate();

        // Stockage via le provider (qui utilise Flysystem en interne)
        $stored = $provider->store($request);

        $request->validateFileConstraints($stored->mimeType, $stored->size);

        $hash = $this->calculateHash($request);

        $fileObject = new FileObject(
            filename: $stored->filename,
            mimeType: $stored->mimeType,
            size: $stored->size,
            storageDriver: $driverName,
            source: $request->source,
            storagePath: $stored->storagePath,
            hash: $hash,
            metadata: $stored->metadata
        );

        $this->em->persist($fileObject);
        $this->em->flush();

        $this->eventDispatcher->dispatch(
            new FileUploadedEvent($fileObject, $request->source),
            FileUploadedEvent::NAME
        );

        return $fileObject;
    }

    public function getPublicUrl(FileObject $file): string
    {
        $provider = $this->getProvider($file->getStorageDriver());
        $url = $provider->getPublicUrl($file->getStoragePath());

        if ($this->requestStack->getCurrentRequest() instanceof Request) {
            return $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost() . $url;
        }

        return $url;
    }

    public function delete(FileObject $file, bool $hardDelete = false): void
    {
        if ($hardDelete) {
            $provider = $this->getProvider($file->getStorageDriver());
            $provider->delete($file->getStoragePath());

            $this->em->remove($file);
        } else {
            $file->softDelete();
        }

        $this->em->flush();

        $this->eventDispatcher->dispatch(
            new FileDeletedEvent($file, $hardDelete),
            FileDeletedEvent::NAME
        );
    }

    public function findDuplicate(string $hash): ?FileObject
    {
        return $this->em->getRepository(FileObject::class)
            ->findOneBy(['hash' => $hash, 'deletedAt' => null]);
    }

    public function read(FileObject $file): string
    {
        $provider = $this->getProvider($file->getStorageDriver());
        return $provider->read($file->getStoragePath());
    }

    private function getProvider(string $name): FileStorageProvider
    {
        if (!isset($this->providers[$name])) {
            throw new StorageException("Storage provider '{$name}' not found");
        }

        return $this->providers[$name];
    }

    private function calculateHash(FileUploadRequest $request): ?string
    {
        $path = $request->localPath;

        if (!$path || !file_exists($path)) {
            return null;
        }

        return hash_file('sha256', $path);
    }
}

