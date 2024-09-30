<?php

namespace App\Controller;
use App\Entity\Documents;
use App\Entity\Formations;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(EntityManagerInterface $entityManager): Response
    {// get info in entities
        $formations = $entityManager->getRepository(Formations::class)->findAll();
        $formateurs = $entityManager->getRepository(User::class)->findall();
        $documents = $entityManager->getRepository(Documents::class)->findAll();
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
            'formations' => $formations,
            'formateurs' => $formateurs,
            'documents' => $documents,
        ]);
    }


}
