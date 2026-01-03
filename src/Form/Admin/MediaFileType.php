<?php

namespace App\Form\Admin;

use App\Entity\Media\FileObject;
use App\Entity\Media\MediaInterface;
use App\Entity\Media\ValueObject\FileSource;
use App\Feature\Media\DTO\FileUploadRequest;
use App\Feature\Media\Service\FileManagerInterface;
use App\Form\DataTransformer\MediaToFileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * FormType custom pour gérer l'upload de fichiers via le FileManager existant
 */
class MediaFileType extends AbstractType
{
    public function __construct(
        private readonly FileManagerInterface $fileManager
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajoute le DataTransformer pour gérer la conversion MediaInterface <-> FileObject
        $builder->addModelTransformer(new MediaToFileTransformer());

        // Event listener pour l'upload du fichier
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options) {
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $event->getData();

            if (!$uploadedFile instanceof UploadedFile) {
                return;
            }

            // Création de la requête d'upload
            $uploadRequest = new FileUploadRequest(FileSource::UPLOAD);
            $uploadRequest->localPath = $uploadedFile->getPathname();
            $uploadRequest->originalFilename = $uploadedFile->getClientOriginalName();

            // Application des contraintes depuis les options du field
            if (!empty($options['allowedMimeTypes'])) {
                $uploadRequest->allowedMimeTypes = $options['allowedMimeTypes'];
            }

            if ($options['maxSize']) {
                $uploadRequest->maxSize = $options['maxSize'];
            }

            // Options de stockage
            $uploadRequest->options['customFolder'] = $options['uploadDir'];

            // Upload via le FileManager
            $fileObject = $this->fileManager->upload(
                $uploadRequest,
                $options['storageDriver']
            );

            // Retourne le FileObject créé
            $event->setData($fileObject);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // Pas de data_class, on gère manuellement
            'allowedMimeTypes' => [],
            'maxSize' => 5 * 1024 * 1024, // 5MB
            'uploadDir' => 'uploads',
            'storageDriver' => 'local',
            'showPreview' => true,
            'basePath' => '/media',
            'required' => false,
            'mapped' => true, // On mappe maintenant pour que EasyAdmin assigne automatiquement
            'attr' => function (Options $options) {
                // Passe les options en attributs HTML pour le template
                return [
                    'data-max-size' => $options['maxSize'],
                    'data-allowed-types' => implode(',', $options['allowedMimeTypes']),
                ];
            },
        ]);

        $resolver->setAllowedTypes('allowedMimeTypes', 'array');
        $resolver->setAllowedTypes('maxSize', 'int');
        $resolver->setAllowedTypes('uploadDir', 'string');
        $resolver->setAllowedTypes('storageDriver', 'string');
        $resolver->setAllowedTypes('showPreview', 'bool');
    }

    public function getParent(): string
    {
        return FileType::class;
    }
}
