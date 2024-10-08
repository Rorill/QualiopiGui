<?php

namespace App\Form;

namespace App\Form;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\SecurityBundle\Security;


class AssociatedFormateurType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('formateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getLastName() . ' ' . $user->getFirstName(); // Combiner le nom et le prénom
                },
                'placeholder' => 'Sélectionner un formateur',
                'query_builder' => function (EntityRepository $er) {
                    // Récupérer l'utilisateur connecté
                    $currentUser = $this->security->getUser();

                    return $er->createQueryBuilder('f')
                        ->where('f.id != :adminId') // Exclure l'admin connecté
                        ->setParameter('adminId', $currentUser->getId());
                },
            ])
            ->add('submit', SubmitType::class, ['label' => 'Associer Formateur']);
    }

}
