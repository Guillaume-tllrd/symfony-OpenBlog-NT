<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureService
{

    // on récupére les paramètre du service.yaml
    public function __construct(
        private ParameterBagInterface $params
    ) {}

    // méthode qui permet de créer une image carré à partir de n'importe quel format
    public function square(UploadedFile $picture, ?string $folder = '', ?int $width = 250): string
    {

        // on donne un nouveau nom à l'mage:
        $file = md5(uniqid(rand(), true)) . '.webp';

        //on récupère les infos de l'image:
        $pictureInfos = getimagesize($picture);


        if ($pictureInfos === false) {
            throw new Exception('Format d\'image incorrect');
        }

        // on vérifie le type mime:
        switch ($pictureInfos['mime']) {
            case 'image/png':
                $sourcePicture = imagecreatefrompng($picture);
                break;
            case 'image/jpeg':
                $sourcePicture = imagecreatefromjpeg($picture);
                break;
            case 'image/webp':
                $sourcePicture = imagecreatefromwebp($picture);
                break;
            default:
                throw new Exception('Format d\'image incorrect');
        }

        // on recadre l'image pour cela il faut connaitre sa hauteur et largeur:
        $imageWidth = $pictureInfos[0];
        $imageHeight = $pictureInfos[1];

        switch ($imageWidth <=> $imageHeight) {
                // on utilise un switch pour la comparaison si la width est inférieur ou égal ou supérieur à la height
            case -1: // la largeur est plus petite que la hauteur (portrait)
                $squareSize = $imageWidth;
                $srcX = 0;
                $srcY = ($imageHeight - $imageWidth) / 2;
                break;
            case 0: // carré
                $squareSize = $imageWidth;
                $srcX = 0;
                $srcY = 0;
                break;

            case 1: //paysage
                $squareSize = $imageWidth;
                $srcX = ($imageWidth - $imageHeight) / 2;
                $srcY = 0;
                break;
        }

        // on crée une nouvelle image vierge:
        $resizedPicture = imagecreatetruecolor($width, $width);

        // On génère le contenu de l'image, être très vigoureux qd on remplit la function
        imagecopyresampled($resizedPicture, $sourcePicture, 0, 0, $srcX, $srcY, $width, $width, $squareSize, $squareSize);

        // on crée le chemin de stockage, on prend le paramètre crée dans services.yaml et concatène folder qui repnd article dans le controller cad que l'image va se stocker dans le dossier upload puis dans le dossier article
        $path = $this->params->get('uploads_directory') . $folder;

        // on crée le dossier s'il n'existe pas:
        if (!file_exists($path . '/mini/')) {
            mkdir($path . '/mini/', 0755, true);
        }

        //on stocke l'image réduite:
        imagewebp($resizedPicture, $path . '/mini/' . $width . 'x' . $width . '-' . $file);

        //on stocke l'image originale:
        $picture->move($path . '/', $file);

        return $file;
    }
}
