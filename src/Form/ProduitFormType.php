<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Categorie;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\File;

class ProduitFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class,
            ['label' => 'Nom'])
            ->add('produitCategorie', EntityType::class,
            ['class' => Categorie::class,
             'choice_label' => 'categorie',
             'label' => 'Catégorie'
            ])
            ->add('prix', MoneyType::class,
            ['label' => 'Prix De Vente',
             'html5' => true,
             'currency' => 'CAD',
             'help' => 'Prix affiché avant taxe'
            ])
            ->add('quantiteEnStock', NumberType::class,
            ['label' => 'Quantité En Inventaire',
            'html5' => true,])
            ->add('description', TextareaType::class,
            ['label' => 'Description'])
            ->add('imagePath', FileType::class,
            ['label' => 'Image Du Produit',  
             'required' => false,
             'mapped' => false,
             'constraints' => [
                new File([
                    'maxSize' => '1024K',
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg'
                    ],
                    'mimeTypesMessage' => 'Téléverser une image valide.'
                ])
             ]
            ])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Produit::class
        ]);
    }
}
