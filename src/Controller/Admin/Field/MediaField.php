<?php

namespace App\Controller\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

/**
 * Custom EasyAdmin Field pour gérer les uploads via le système Media existant
 *
 * Usage:
 * MediaField::new('logo')
 *     ->setLabel('Logo')
 *     ->onlyImages()
 *     ->setMaxSize(2 * 1024 * 1024) // 2MB
 *     ->setUploadDir('merchants/logos')
 */
final class MediaField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_ALLOWED_MIME_TYPES = 'allowedMimeTypes';
    public const OPTION_MAX_SIZE = 'maxSize';
    public const OPTION_UPLOAD_DIR = 'uploadDir';
    public const OPTION_STORAGE_DRIVER = 'storageDriver';
    public const OPTION_SHOW_PREVIEW = 'showPreview';
    public const OPTION_BASE_PATH = 'basePath';

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('admin/field/media.html.twig') // Pour index/detail
            ->addFormTheme('admin/field/media_widget.html.twig') // Pour edit/new
            ->setFormType(\App\Form\Admin\MediaUploadType::class)
            ->addCssClass('field-media')
            ->setCustomOptions([
                self::OPTION_ALLOWED_MIME_TYPES => [],
                self::OPTION_MAX_SIZE => 5 * 1024 * 1024, // 5MB par défaut
                self::OPTION_UPLOAD_DIR => 'uploads',
                self::OPTION_STORAGE_DRIVER => 'local',
                self::OPTION_SHOW_PREVIEW => true,
                self::OPTION_BASE_PATH => '/uploads',
            ]);
    }

    /**
     * Limite aux images uniquement (jpg, png, gif, webp)
     */
    public function onlyImages(): self
    {
        $this->setCustomOption(self::OPTION_ALLOWED_MIME_TYPES, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ]);

        return $this;
    }

    /**
     * Définit les types MIME autorisés
     */
    public function setAllowedMimeTypes(array $mimeTypes): self
    {
        $this->setCustomOption(self::OPTION_ALLOWED_MIME_TYPES, $mimeTypes);
        return $this;
    }

    /**
     * Définit la taille maximale en bytes
     */
    public function setMaxSize(int $bytes): self
    {
        $this->setCustomOption(self::OPTION_MAX_SIZE, $bytes);
        return $this;
    }

    /**
     * Définit le dossier de destination (ex: 'merchants/logos')
     */
    public function setUploadDir(string $dir): self
    {
        $this->setCustomOption(self::OPTION_UPLOAD_DIR, $dir);
        return $this;
    }

    /**
     * Définit le driver de stockage (local, s3, etc.)
     */
    public function setStorageDriver(string $driver): self
    {
        $this->setCustomOption(self::OPTION_STORAGE_DRIVER, $driver);
        return $this;
    }

    /**
     * Active/désactive la preview de l'image
     */
    public function showPreview(bool $show = true): self
    {
        $this->setCustomOption(self::OPTION_SHOW_PREVIEW, $show);
        return $this;
    }

    /**
     * Définit le chemin de base pour les URLs publiques
     */
    public function setBasePath(string $path): self
    {
        $this->setCustomOption(self::OPTION_BASE_PATH, $path);
        return $this;
    }
}
