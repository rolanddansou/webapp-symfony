<?php

namespace App\Form\DataMapper;

use App\Entity\Media\FileObject;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;

/**
 * DataMapper pour MediaUploadType
 *
 * Gère la conversion bidirectionnelle entre:
 * - FileObject|null (valeur de l'entité)
 * - Formulaire compound avec sous-champs ['file', 'delete']
 */
class MediaUploadDataMapper implements DataMapperInterface
{
    private ?FileObject $originalValue = null;

    /**
     * Maps properties of some data to a list of forms.
     *
     * @param FileObject|null $viewData - L'objet FileObject existant (ou null)
     * @param FormInterface[] $forms - Les sous-formulaires ['file', 'delete']
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        // Sauvegarde la valeur originale pour la réutiliser dans mapFormsToData
        $this->originalValue = $viewData;

        // $viewData est le FileObject existant (ou null si pas d'image)
        if (null === $viewData) {
            return;
        }

        if (!$viewData instanceof FileObject) {
            throw new UnexpectedTypeException($viewData, FileObject::class);
        }

        $forms = iterator_to_array($forms);

        // On ne peut pas pré-remplir un champ file, donc on laisse vide
        // Le champ 'delete' est toujours décoché par défaut
        $forms['file']->setData(null);
        $forms['delete']->setData(false);
    }

    /**
     * Maps the data of a list of forms into the properties of some data.
     *
     * @param FormInterface[] $forms - Les sous-formulaires ['file', 'delete']
     * @param FileObject|null &$viewData - La valeur à assigner à l'entité
     */
    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        $deleteChecked = $forms['delete']->getData();
        $uploadedFile = $forms['file']->getData();

        // Si "delete" est coché, on retourne null (suppression)
        if ($deleteChecked) {
            $viewData = null;
            return;
        }

        // Si un nouveau fichier a été uploadé, on le retourne
        if ($uploadedFile instanceof FileObject) {
            $viewData = $uploadedFile;
            return;
        }

        // Sinon, on garde la valeur originale (pas de changement)
        $viewData = $this->originalValue;
    }
}
