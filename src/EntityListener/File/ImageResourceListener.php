<?php

namespace App\EntityListener\File;

use App\Entity\File\ImageResource;
use App\Feature\Helper\FileHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\LazyString;
use Vich\UploaderBundle\Storage\StorageInterface;

class ImageResourceListener
{
    /**
     * @var StorageInterface
     */
    private StorageInterface $storage;
    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    public function __construct(StorageInterface $storage, RequestStack $requestStack)
    {
        $this->storage = $storage;
        $this->requestStack = $requestStack;
    }

    public function postLoad(ImageResource $object): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return;
        }

        if($object->getImage()?->getName()){
            $path = LazyString::fromCallable(function() use($request, $object) {
                return $request->getSchemeAndHttpHost().$this->storage->resolveUri($object, 'imageFile');
            });

            $object->url= $path;

            $filePath= $object->getImage()?->getName();
            $thumb= __DIR__."/../../public/upload/thumb/".$filePath;

            if(file_exists($thumb)){
                $path1 = LazyString::fromCallable(function() use($request, $filePath) {
                    return $request->getSchemeAndHttpHost()."/upload/thumb/".$filePath;
                });

                $object->thumb= $path1;
            }else{
                $object->thumb= $path;
            }
        }
    }

    public function preFlush(ImageResource $resource): void
    {
        $file= $resource->getImageFile();
        if($file){
            try {
                FileHelper::thumbnailImage($file, "upload/thumb/");
            } catch (\Exception $e) {}
        }
    }
}
