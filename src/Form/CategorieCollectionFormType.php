<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CategorieCollectionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categories', CollectionType::class, [
                'entry_type' => CategorieType::class,
                'allow_add' => true
            ])
            ->add('btnSave', SubmitType::class, [
                'label' => 'Enregistrer',
                'row_attr' => ['class' => 'form-button col-3'],
                'attr' => ['class' => 'btnSave btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'categories_collection_form'
        ]);
    }
}
