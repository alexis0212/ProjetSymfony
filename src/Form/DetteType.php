<?php

namespace App\Form;

use App\Entity\Dette;
use App\Entity\Client;
use App\Entity\Article;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la dette',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('montant', NumberType::class, [
                'label' => 'Montant total',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le montant total',
                ],
            ])
            ->add('montantVerser', NumberType::class, [
                'label' => 'Montant versé',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le montant versé',
                ],
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'surname',
                'label' => 'Client',
                'placeholder' => 'Sélectionnez un client',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('articles', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'nomArticle',
                'label' => 'Articles associés',
                'multiple' => true,
                'expanded' => true, // Utiliser un menu déroulant multisélection
                'required' => true,
                'attr' => [
                    'class' => 'form-select', // Classe Bootstrap pour un menu déroulant
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer la dette',
                'attr' => [
                    'class' => 'btn btn-primary mt-3',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dette::class,
        ]);
    }
}
