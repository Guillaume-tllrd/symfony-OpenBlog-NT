<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Keywords;
use App\Entity\Posts;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddPostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'article',
                // on peut lui rajouter un contrainte en disant que le titre ne peut pas être vide avec new NotBlank
                'constraints' => [
                    new NotBlank()
                ]
            ])
            // ->add('slug')
            ->add('content', TextareaType::class, [
                'label' => 'Contenu de l\'article'
            ]) // pour changer l'input de content en textarea on fait un TextareaType
            ->add('featuredImage', FileType::class, [
                'label' => 'Image de l\'article',
                'mapped' => false,
                // new Image ici est une contrainte de fichier use Symfony\Component\Validator\Constraints\Image;
                // si on veut insérer plusieurs images il faudrait rajouter "multiple" => true, et faire un new All() et mettre new Image à l'intérieur
                'constraints' => [
                    new Image(
                        minWidth: 200,
                        maxWidth: 4000,
                        minHeight: 200,
                        maxHeight: 4000,
                        allowPortrait: false, // image que en paysage
                        mimeTypes: [
                            "image/jpeg",
                            "image/png"
                        ]
                    )
                ]
            ])
            // ->add('users', EntityType::class, [
            //     'class' => Users::class,
            //     'choice_label' => 'id',
            // ])
            ->add('categories', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'name',
                'multiple' => true, //pour pouvoir en choisir plusieurs
                'expanded' => true // pour avoir des cases à cocher
            ])
            ->add('keywords', EntityType::class, [
                'class' => Keywords::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Posts::class,
        ]);
    }
}
