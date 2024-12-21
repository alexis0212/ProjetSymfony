<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('surname', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom'],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le téléphone'],
            ])
            ->add('adress', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez l’adresse'],
            ])
            ->add('createAccount', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Créer un compte',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('email', EmailType::class, [
                'mapped' => false,
                'label' => 'Login',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le login'],
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le mot de passe'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
