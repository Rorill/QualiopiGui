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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Knp\Component\Pager\PaginatorInterface;
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
    public function uploadDocument(int $id, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
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
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData(); // Récupérer le fichier uploadé

            // Vérifier si un document existe déjà pour cette formation et ce formateur
            $existingDocument = $entityManager->getRepository(Documents::class)->findOneBy([
                'Formation' => $formation,
                'Formateur' => $this->getUser(),
                'category' => $document->getCategory(), // Assure-toi de comparer aussi par type de document
            ]);

            if ($existingDocument) {
                // Supprimer le fichier physique de l'ancien document
                $oldFilePath = $this->getParameter('documents_directory') . '/' . $existingDocument->getTitle();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath); // Supprime l'ancien fichier
                }

                // Met à jour l'ancien document
                $document = $existingDocument; // Remplace le nouveau document par l'existant
            }

            if ($file) {
                $userDefinedName = $form->get('title')->getData(); // Récupérer le nom entré par l'utilisateur
                // Utiliser SluggerInterface pour rendre le nom de fichier sûr
                $safeFilename = $slugger->slug($userDefinedName);
                $extension = $file->guessExtension();

                if (!$extension) {
                    $extension = $file->getClientOriginalExtension();
                }

                // Générer un nouveau nom de fichier avec une extension fiable
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;


                try {
                    // Déplacer le fichier vers le répertoire défini
                    $file->move(
                        $this->getParameter('documents_directory'), // Utiliser le paramètre configuré
                        $newFilename
                    );

                    // debug after deplaced
                    dump("Fichier déplacé avec succès : " . $newFilename);


                } catch (FileException $e) {
                    // Gérer les exceptions si nécessaire (par exemple, erreur de permission d'écriture)
                    dump("Erreur lors du déplacement du fichier : " . $e->getMessage());
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload du fichier.');
                    return $this->redirectToRoute('app_upload_docs', ['id' => $formation->getId()]);
                }

                // Met à jour l'entité `Document` avec le nom du fichier ou le chemin
                $document->setTitle(trim($newFilename));
            }

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

    #[Route('/user/document/{id}/download', name: 'app_document_download', methods: ['GET'])]
    public function downloadDocument(int $id,EntityManagerInterface $entityManager): Response
    {
        // get doc per ID

        $document = $entityManager->getRepository(Documents::class)->find($id);

        // check if doc exist
        if (!$document) {
            throw $this->createNotFoundException('Document non trouvé');
        }
        // get the file name
        $fileName = $document->getTitle();

        if (!pathinfo($fileName, PATHINFO_EXTENSION)) {
            // Deviner l'extension si elle n'est pas présente
            $extension = $document->getFileExtension(); // Si une méthode getFileExtension() est présente, sinon à adapter
            $fileName .= '.' . $extension;
        }
        // get complete doc filepath
        $filepath = $this->getParameter('documents_directory') . '/' . $document->getTitle();

        if (!file_exists($filepath)) {
            throw $this->createNotFoundException('fichier non trouvé sur le serveur');
        }

        $response = new BinaryFileResponse($filepath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $document->getTitle());

        return $response;

    }


}
