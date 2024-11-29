<?php
// on  construiit manuellement cette page
namespace App\Controller\Api;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api', name: 'api_')]

class FileUploadController
{
    // comme on n'utilse pas extend AbstractController on doit mettre des paramètre dans le constructeur
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly UrlGeneratorInterface $router
    ) {}
    #[Route('/file/upload', name: 'file_upload', defaults: ['_format=json'], methods: ['post'])]
    public function fileUpload(Request $request): JsonResponse
    {
        // on va chercher request dans httpFoundation
        $fichier = $request->files->get('upload');

        // on prend le nom de notre fichier complet et va l'exploser:
        $ext = explode('.', strtolower($fichier->getClientOriginalName()));
        // dans extension on prend la fin de notre tableau cad .jpg ect.
        $extension = end($ext);

        $newName = md5(uniqid()) . '.' . $extension;
        // je vais chercher uploads_directory dans services.yaml
        $path = $this->params->get('uploads_diretory') . 'content/';

        // si le fhicheir n'existe pas je le crée, je lui donne les permission et true pour dire qu'il fazut le créer
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $fichier->move($path, $newName);

        //on génère le lien: ca contient l'url complète de l'image
        $link = $this->router->generate('app_main', [], UrlGeneratorInterface::ABSOLUTE_URL) . 'uploads/content/' . $newName;

        $response = ['url' => $link];

        return new JsonResponse($response);
    }
} 
//Arrêté à 25:00
