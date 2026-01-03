<?php

namespace App\Feature\Helper;

use Symfony\Component\HttpFoundation\File\File;

class FileHelper
{
    public static function resizeImage(File $file, $width = 1520, $height = 1520){
        // 🔍 Vérification que Imagick est bien disponible
        if (!class_exists('\Imagick')) {
            throw new \Exception("L'extension Imagick n'est pas installée ou activée sur ce serveur.");
        }

        $file = urldecode($file);
        if(is_file($file)) {
            $size = ['width'=>0,'height'=>0];
            list($size['width'], $size['height']) = getimagesize(realpath($file));
            if($size['width'] > 600 || $size['height'] > 600){
                try{
                    $imagick = new \Imagick(realpath($file));
                    $imagick->setImageCompressionQuality(75);
                    $imagick->thumbnailImage($width, $width, 1, false);
                    $imagick->writeimage(realpath($file));
                    $imagick->clear();
                    $imagick->destroy();
                }catch (\Exception $e){

                }
            }
            return $file;
        }
        else
            throw new \Exception("Veuillez choisir une image valide et réessayer => ".$file);
    }

    public static function thumbnailImage(File $file, $target, $quality = 50){

        // 🔍 Vérification que Imagick est bien disponible
        if (!class_exists('\Imagick')) {
            throw new \Exception("L'extension Imagick n'est pas installée ou activée sur ce serveur.");
        }

        $file = urldecode($file);
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $filename = pathinfo($file, PATHINFO_FILENAME);
        if (is_file($file)) {
            try{
                $imagick = new \Imagick(($file));
                if($imagick->getImageWidth() > 600 || $imagick->getImageHeight() > 600){
                    $imagick->setImageFormat(strtolower($extension));
                    $imagick->setImageCompressionQuality($quality);
                    $imagick->thumbnailImage(600, 600, 1, false);
                    $imagick->writeimage(realpath($target).'/'.$filename.'.'.$extension);
                }
                $imagick->clear();
                $imagick->destroy();
            }catch (\Exception $e){
            }
            return true;
        }
        else
            throw new \Exception("Veuillez choisir une image valide et réessayer.");
    }
}
