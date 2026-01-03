<?php

namespace App\Form\Admin;

use App\Form\DataMapper\MediaUploadDataMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType wrapper pour MediaFileType avec checkbox de suppression
 * Structure:
 * - file: champ upload (MediaFileType)
 * - delete: checkbox "Supprimer l'image existante"
 *
 * Utilise un DataMapper personnalisé pour gérer la conversion
 * FileObject <-> array['file', 'delete']
 */
class MediaUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Champ d'upload
        $builder->add('file', MediaFileType::class, [
            'label' => false,
            'allowedMimeTypes' => $options['allowedMimeTypes'],
            'maxSize' => $options['maxSize'],
            'uploadDir' => $options['uploadDir'],
            'storageDriver' => $options['storageDriver'],
            'showPreview' => $options['showPreview'],
            'required' => false,
        ]);

        // Checkbox de suppression (seulement si on a déjà une image)
        $builder->add('delete', CheckboxType::class, [
            'label' => 'Supprimer l\'image existante',
            'required' => false,
            'attr' => ['class' => 'form-check-input'],
        ]);

        // Utilise un DataMapper personnalisé pour gérer la conversion
        $builder->setDataMapper(new MediaUploadDataMapper());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'allowedMimeTypes' => [],
            'maxSize' => 5 * 1024 * 1024,
            'uploadDir' => 'uploads',
            'storageDriver' => 'local',
            'showPreview' => true,
            'basePath' => '/uploads',
            'required' => false,
            'mapped' => true,
            'compound' => true,
            'attr' => function (Options $options) {
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
}
