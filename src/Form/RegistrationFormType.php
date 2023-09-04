<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends ModificationFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('courriel', EmailType::class, [ 
                'required' => true, 
                'label' => 'Courriel',
                 'attr' => []])                   
            ->add('password', RepeatedType::class, [ 
                'type' => PasswordType::class, 
                'invalid_message' => "* Les mots de passe doivent être identiques", 
                'constraints' => [new Assert\Length(min:6, minMessage:"* Le mot de passe doit contenir au moins {{ limit }} caractères")], 
                'options' => ['attr' => ['class' => 'password-field']],
                 'required' => true, 'first_options' => ['label' => "Mot de passe"], 
                 'second_options' => ['label' => "Confirmation du mot de passe"]])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J\'accepte les modalités et les conditions d\'utilisation du site CosmeticsIA.',
                'constraints' => [
                        new IsTrue([
                            'message' => '* Acceptez les modalités et conditions générales pour continuer.',
                        ]),
                    ]
                ])
            ->add('valider', SubmitType::class, [ 
                'label' => "Créer votre compte",
                'row_attr' => ['class' => 'form-button'],
                'attr' => ['class' => 'btn-primary']]);
    }
}
