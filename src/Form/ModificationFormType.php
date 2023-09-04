<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

use Symfony\Component\Validator\Constraints as Assert;

class ModificationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [ 
                'required' => true, 
                'label' => 'Nom',
                'attr' => []])
            ->add('prenom', TextType::class, [ 
                'required' => true, 
                'label' => 'Prénom',
                'attr' => []])
            ->add('adresse', TextType::class, [ 
                'required' => true, 
                'label' => 'Adresse',
                'attr' => []])
            ->add('ville', TextType::class, [ 
                'required' => true, 
                'label' => 'Ville',
                'attr' => []])
            ->add('codePostal', TextType::class, [ 
                'required' => true, 
                'label' => 'Code Postal',
                'attr' => []])
            ->add('province', ChoiceType::class, [ 
                'label' => 'Province',
                'required' => true,
                'choices' => [
                    'AB' => 'AB',
                    'BC' => 'BC',
                    'PE' => 'PE',
                    'MB' => 'MB',
                    'NB' => 'NB',
                    'NS' => 'NS',
                    'NU' => 'NU',
                    'ON' => 'ON',
                    'QC' => 'QC',
                    'SK' => 'SK',
                    'NL' => 'NL',
                    'YT' => 'YT',
                    'NT' => 'NT',
             ]])
            ->add('telephone', TextType::class, [ 
                'required' => false, 
                'label' => 'Téléphone',
                'attr' => []])                         
            ->add('valider', SubmitType::class, [ 
                'label' => "Sauvegarder",
                'row_attr' => ['class' => 'form-button'],
                'attr' => ['class' => 'btn-primary']]);

        $builder->get('telephone')->addModelTransformer(new CallbackTransformer( 
                function($phoneFromDatabase) { 
                    // To view 
                    $newPhone = substr_replace($phoneFromDatabase, '-', 3, 0); 
                    return substr_replace($newPhone, '-', 7, 0); 
                }, 
                    function($phoneFromView) { 
                    // To Database
                     return str_replace('-','', $phoneFromView);
                } 
        ));
        
        $builder->get('codePostal')->addModelTransformer(new CallbackTransformer( 
                function($cpFromDatabase) { 
                    // To view 
                    return str_replace(' ','',$cpFromDatabase); 
                }, 
                    function($cpFromView) { 
                    // To Database
                    return str_replace(' ','', $cpFromView);
                } 
        ));        

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
