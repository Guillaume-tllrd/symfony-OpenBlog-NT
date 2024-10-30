<?php

namespace App\Form;

use App\Entity\Keywords;
use App\Entity\Posts;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddKeywordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            // ->add('slug') et slug va être généré automatiquelent avec le name qu'on mettra en miniscule
            // automatiquement il a crée un post car il ya une relation mais on doit pouvoir créer un keyword sans mettre de post donc on peut l'enlever
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
            'data_class' => Keywords::class,
        ]);
    }
}
