<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Form\AddCategoriesFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/categories', name: 'app_admin_categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/categories/index.html.twig', [
            'controller_name' => 'CategoriesController',
        ]);
    }

    #[Route('/ajouter', name: 'add')]
    public function addCategory(
        Request $request,
        SluggerInterface $slugger,
        EntityManagerInterface $em
    ): Response {

        // Voir la page keyword, car même principe
        $category = new Categories();

        $categoryForm = $this->createForm(AddCategoriesFormType::class, $category);

        $categoryForm->handleRequest($request);

        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            //on génére le slug
            $slug = strtolower($slugger->slug($category->getName()));

            // on ajoute ke slug à la catégorie:
            $category->setSlug($slug);

            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'la catégorie a été créé');
            return $this->redirectToRoute('app_admin_categories_index');
        }

        return $this->render('admin/categories/add.html.twig', [
            'categoryForm' => $categoryForm->createView(),
        ]);
    }
}
