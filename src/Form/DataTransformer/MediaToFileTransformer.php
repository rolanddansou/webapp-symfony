<?php

namespace App\Form\DataTransformer;

use App\Entity\Media\FileObject;
use App\Entity\Media\MediaInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforme un MediaInterface en null pour le formulaire,
 * et un FileObject uploadé en MediaInterface pour l'entité
 */
class MediaToFileTransformer implements DataTransformerInterface
{
    /**
     * Transforme MediaInterface -> null (pour afficher dans le formulaire)
     * On ne transforme pas vraiment, on laisse null car le champ file est vide au chargement
     *
     * @param MediaInterface|null $value
     * @return null
     */
    public function transform(mixed $value): mixed
    {
        // Quand on charge le formulaire en mode EDIT, on a un MediaInterface existant
        // Mais le champ file doit être vide (on ne peut pas pré-remplir un input file)
        // Donc on retourne null
        return null;
    }

    /**
     * Transforme UploadedFile/FileObject -> FileObject (pour l'entité)
     *
     * @param FileObject|null $value Le FileObject créé par MediaFileType
     * @return FileObject|null
     */
    public function reverseTransform(mixed $value): mixed
    {
        // Si aucun fichier n'a été uploadé, on retourne null
        // (l'entité gardera son fichier existant)
        if ($value === null) {
            return null;
        }

        // Si c'est déjà un FileObject (créé par MediaFileType), on le retourne tel quel
        if ($value instanceof FileObject) {
            return $value;
        }

        // Cas inattendu
        throw new TransformationFailedException(sprintf(
            'Expected a FileObject or null, got "%s"',
            get_debug_type($value)
        ));
    }
}
