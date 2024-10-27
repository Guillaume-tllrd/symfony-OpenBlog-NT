<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route as AnnotationRoute;
use Symfony\Component\Routing\Attribute\Route;
// cette page a été conçub avec le make bundler dans le terminal symfony console make:controller, fait également une page dans le dossier template
class MainController extends AbstractController
{
    // prend la route de la page d'accueil, elle s'appele app_main
    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        // le this->render est amené par l'abstractcontroller, si on avait pas AbstractController il faudrait charger twig et faire le render avec twig pour charger le fichier dans main index.html.twig
        return $this->render('main/index.html.twig', [
            'prenom' => 'Guillaume',
            // on peut changer le nom j'ai mis Guillaume
            // entre les accolades dans main/twig ce sont des variables qui sont passé par le render du controller
        ]);
    }

    // si je veux faire une autre route je fais une autre fonction :
    #[Route('/mentions-legales', name: 'app_mentions')]
    // pour que le route soit prise dans le css elle doit être dans templates/main/et faire un fichier twig
    public function mentions(): Response
    {
        return $this->render('main/mentions.html.twig');
    }
}
