<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Posts;
use App\Repository\CategoriesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddCategoriesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la catégorie'
            ]) // pour rajouter des paramètres à l'input par ex le label on utilise TextType::class
            // ->add('slug')
            ->add('parent', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'name',
                // pour que à l'intérieur du select de "parent" on est nom et pas l'id c'est plus pertinent
                'placeholder' => '--Pas de parent--',
                // pour qu'on puisse ne pas sélectionner de parent on ajoute un placeholder et on enlève le required
                'required' => false,
                // pour trier par odre alphabétique on utilise un générateur de requête query_builder en faisant une function qui prend $cr en argument et on lui dit de retourner categorie trier par le nom en asc
                'query_builder' => function (CategoriesRepository $cr) {
                    return $cr->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                }
            ])
            // ->add('posts', EntityType::class, [
            //     'class' => Posts::class,
            //     'choice_label' => 'id',
            //     'multiple' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}
