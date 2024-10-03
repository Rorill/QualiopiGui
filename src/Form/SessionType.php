<?php

namespace App\Form;

use App\Entity\Formations;
use App\Entity\Location;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'City',
                'label' => 'Lieu',
                'placeholder' => 'Sélectionner un lieu',
            ])
            ->add('starting_date', null, [
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('ending_date', null, [
                'label' => 'Date de Fin',
                'widget' => 'single_text',
            ])

            ->add('name')

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formations::class,
        ]);
    }
}
