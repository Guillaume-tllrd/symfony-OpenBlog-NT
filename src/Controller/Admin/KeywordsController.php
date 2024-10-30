<?php

namespace App\Controller\Admin;

use App\Entity\Keywords;
use App\Form\AddKeywordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

// on peut aller dans l'url /admin/keywords
#[Route('/admin/keywords', name: 'app_admin_keywords_')]
class KeywordsController extends AbstractController
// comme notre controller extends AbstractController on a plusieurs méthode disponible
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/keywords/index.html.twig', [
            'controller_name' => 'KeywordsController',
        ]);
    }
    // l'url fera: /admin/keywords/ajouter
    // j'ai créé la route mtn il faut mon formulaire, on va utiliser le budnler pour le créer et qui va s'installer dans le dossier Form
    #[Route('/ajouter', name: 'add')]
    public function addKeyword(
        Request $request,
        SluggerInterface $slugger,
        EntityManagerInterface $em
    ): Response {

        //  on initialise un mot clé:
        $keyword = new Keywords();

        // on initiliase notre form et on a acces grace à Abstract à createForm qui nous demande un string donc on met la class du form plus $keyword
        $keywordForm = $this->createForm(AddKeywordFormType::class, $keyword);

        // pour traiter notre form, il faut que l'on récupère notre requete(notre $_POST) on utilise l'élément de httpfundation Request que l'on met comme paramètre de addKeyword 
        $keywordForm->handleRequest($request); //vérifie si le form a été envoyé

        // onn vérifie si le form est envoyé et valid
        if ($keywordForm->isSubmitted() && $keywordForm->isValid()) {
            // pour créer notre slug(version simplifié de notre keyword, pour l'injecter dans des url par ex, on utilise SluggerInterface, on fait la méthode slug en récupérant le nom de keyword) pour être sur qu'il soit en miniscule on fait strtolower
            $slug = strtolower($slugger->slug($keyword->getName()));

            // on attribue le slug à notre mot clé avec la méthode setSlug
            $keyword->setSlug($slug);

            // Pour l'écriture en bdd on a besoin de EntityManagerInterface qu'on appel $em et ses méthodes persist et flush
            $em->persist($keyword);
            $em->flush();

            // à partir de là le mot clé est dans la bdd on peut faire un message flash:
            $this->addFlash('success', 'le mot clé a été créé');
            return $this->redirectToRoute('app_admin_keywords_index');
        }

        return $this->render('admin/keywords/add.html.twig', [
            'keywordForm' => $keywordForm->createView(),
            // a gauche c'est le nom de la var que l'on récupéra sur la page twig et droite c'est le formulaire auxquelle on ajoute la méthode createView pour créer la vue html du form
        ]);
    }
}
