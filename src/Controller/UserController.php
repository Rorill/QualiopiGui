<?php

namespace App\Controller;

use App\Entity\Documents;
use App\Entity\Formations;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(Security $security): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
        $formations = $user->getFormations();// Utilise la méthode définie dans l'entité User


        // Assurez-vous que l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login'); // Rediriger vers la page de connexion
        }

        // Renvoyer la vue utilisateur
        return $this->render('user/dashboard.html.twig', [
            'user' => $user, // Passer l'utilisateur à la vue
            'formations' => $formations,
        ]);


    }
    #[Route('/user/formations', name: 'user_formations')]
    #[IsGranted('ROLE_USER')]
    public function userFormations(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        // Récupérer les formations auxquelles l'utilisateur est rattaché
        $sessions = $entityManager->getRepository(Session::class)->findBy(['instructor' => $user]);

        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('filePath')->getData();

            // Upload du fichier
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('documents_directory'), $fileName);
                $document->setFilePath($fileName);
                $document->setUploadedAt(new \DateTime());
                $document->setUser($user);
                $document->setSession($form->get('session')->getData());

                $entityManager->persist($document);
                $entityManager->flush();

                $this->addFlash('success', 'Document uploadé avec succès.');
            }
        }

        return $this->render('user/formation_documents.html.twig', [
            'sessions' => $sessions,
            'form' => $form->createView(),
        ]);
    }


    #[Route("/user/formation/{id}", name: "user_formation_documents")]
    public function formationDocuments(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la formation par ID
        $formation = $entityManager->getRepository(Formations::class)->find($id);

        // Vérifier si la formation existe
        if (!$formation) {
            throw $this->createNotFoundException('Formation non trouvée');
        }

        // Récupérer le formateur connecté
        $formateur = $this->getUser(); // Assurez-vous que cela renvoie un Formateur

        // Récupérer les documents associés à cette formation par le formateur
        $documents = $entityManager->getRepository(Documents::class)->findBy([
            'Formation' => $formation, // Correct
            'Formateur' => $formateur,  // Correct
        ]);

        // Renvoyer la vue avec les documents
        return $this->render('user/formation_documents.html.twig', [
            'formation' => $formation,
            'documents' => $documents,
            'formateur' => $formateur,
        ]);
    }
}






