<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Validator\Constraints as Assert;

class MotDePasseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('motDePasseActuel', PasswordType::class, [ 
            'invalid_message' => "* Mot de passe actuel est invalide."
            ])
        ->add('nouveauMotDePasse', RepeatedType::class, [ 
            'type' => PasswordType::class, 
            'invalid_message' => "* Les mots de passe doivent être identiques", 
            'constraints' => [new Assert\Length(min:6, minMessage:"* Le mot de passe doit contenir au moins {{ limit }} caractères")], 
            'options' => ['attr' => ['class' => 'password-field']],
             'required' => true, 'first_options' => ['label' => "Nouveau mot de passe"], 
             'second_options' => ['label' => "Confirmation du nouveau mot de passe"]])
        ->add('valider', SubmitType::class, [ 
            'label' => "Modifier votre mot de passe",
            'row_attr' => ['class' => 'form-button'],
            'attr' => ['class' => 'btn-primary']]);          
        ;
    }
}
