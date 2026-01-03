<?php

namespace App\EventSubscriber\Admin;

use App\Entity\Media\FileObject;
use App\Entity\Media\MediaInterface;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Gère automatiquement la persistance des FileObject uploadés via MediaField
 *
 * Avec mapped=true dans MediaFileType, Symfony assigne automatiquement
 * le FileObject à l'entité parente. Ce subscriber se contente de persister
 * les FileObject qui ne sont pas encore gérés par Doctrine.
 */
readonly class MediaUploadSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['handleMediaUpload'],
            BeforeEntityUpdatedEvent::class => ['handleMediaUpload'],
        ];
    }

    public function handleMediaUpload(BeforeEntityPersistedEvent|BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        // Utilise la réflexion pour trouver toutes les propriétés de type MediaInterface
        $reflection = new \ReflectionClass($entity);

        foreach ($reflection->getProperties() as $property) {
            // Vérifie si la propriété est de type MediaInterface
            $type = $property->getType();
            if (!$type instanceof \ReflectionNamedType) {
                continue;
            }

            $typeName = $type->getName();
            if ($typeName !== MediaInterface::class && !is_subclass_of($typeName, MediaInterface::class)) {
                continue;
            }

            // Récupère la valeur de la propriété
            $property->setAccessible(true);
            $media = $property->getValue($entity);

            // Si c'est un FileObject qui vient d'être uploadé, on le persiste
            if ($media instanceof FileObject && !$this->em->contains($media)) {
                $this->em->persist($media);
            }
        }
    }
}
