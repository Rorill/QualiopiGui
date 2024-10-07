<?php

namespace App\Controller;

use App\Entity\Formations;
use App\Form\DocumentUploadType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Documents;
use App\Entity\User;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class DocumentsController extends AbstractController
{
    #[Route('/documents', name: 'app_documents')]
    public function index(): Response
    {
        return $this->render('documents/index.html.twig', [
            'controller_name' => 'DocumentsController',
        ]);
    }

    #[Route('/user/formation/{id}/upload', name: 'app_upload_docs')]
    public function uploadDocument(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la formation par ID
        $formation = $entityManager->getRepository(Formations::class)->find($id);

        // Vérifier si la formation existe
        if (!$formation) {
            throw $this->createNotFoundException('Formation non trouvée');
        }

        // Créer un nouveau document
        $document = new Documents();
        $document->setFormation($formation);
        $document->setFormateur($this->getUser()); // Associe le formateur connecté

        // Créer le formulaire d'upload
        $form = $this->createForm(DocumentUploadType::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($document);
            $entityManager->flush();

            $this->addFlash('success', 'Document uploadé avec succès.');

            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/upload_document.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }
}
