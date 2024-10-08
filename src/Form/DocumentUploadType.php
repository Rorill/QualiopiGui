<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Documents;
use App\Entity\Formations;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
class DocumentUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'choisir un fichier',
                'mapped' => false , // Important: DO NOT MAP TO DATABASE
                'required' => true,
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
            ])

            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Sélectionner un type de document',
                'required' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Uploader le document']);


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Documents::class,
        ]);
    }
}
