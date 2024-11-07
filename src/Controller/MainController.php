<?php

namespace App\Controller;

use App\Repository\PostsRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route as AnnotationRoute;
use Symfony\Component\Routing\Attribute\Route;
// cette page a été conçub avec le make bundler dans le terminal symfony console make:controller, fait également une page dans le dossier template
class MainController extends AbstractController
{
    // prend la route de la page d'accueil, elle s'appele app_main
    #[Route('/', name: 'app_main')]
    public function index(PostsRepository $postsRepository, UsersRepository $usersRepository): Response
    {
        // le this->render est amené par l'abstractcontroller, si on avait pas AbstractController il faudrait charger twig et faire le render avec twig pour charger le fichier dans main index.html.twig
        // $prenoms = ['Thomas', 'Guillaume', 'Jean'];
        $lastPost = $postsRepository->findOneBy([], ['id' => 'desc']); // si on veut trouver le dernier post, ne pas oublier de le rajouter dans le compact
        $posts = $postsRepository->findBy([], ['id' => "desc"], limit: 8); // si on veut limiter le nbre d'article on fait findBY sinon findAll pour tous, ensuite on peut transférer à la vue dans le render, ['posts' => $posts] ou avec la méthode compact pour générer le tableau

        $authors = $usersRepository->getUsersByPosts(4); // on indique la limit qu'on lui demande dans la méthode
        return $this->render('main/index.html.twig', compact('lastPost', 'posts', 'authors'));
        // 'prenoms' => $prenoms,
        // on peut changer le nom j'ai mis Guillaume
        // entre les accolades dans main/twig ce sont des variables qui sont passé par le render du controller

    }

    // si je veux faire une autre route je fais une autre fonction :
    #[Route('/mentions-legales', name: 'app_mentions')]
    // pour que le route soit prise dans le css elle doit être dans templates/main/et faire un fichier twig
    public function mentions(): Response
    {
        return $this->render('main/mentions.html.twig');
    }
}
