<?php

namespace App\Controller;

use App\Entity\Documents;
use App\Entity\User;
use App\Entity\Formations;
use App\Form\AssociatedFormateurType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

class FormationsController extends AbstractController
{
    #[Route('/formations', name: 'app_formations')]
    #[IsGranted('ROLE_ADMIN')]
    public function listFormations(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le critère de tri à partir de la requête
        $sortBy = $request->query->get('sort', 'name'); // Défaut : trier par titre
        $order = $request->query->get('order', 'asc'); // Défaut : ordre croissant

        // Valider les paramètres de tri
        if (!in_array($sortBy, ['name', 'starting_date', 'location.name'])) {
            $sortBy = 'name'; // valeur par défaut
        }
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc'; // valeur par défaut
        }

        // Créer une requête pour récupérer les formations
        $query = $entityManager->getRepository(Formations::class)
            ->createQueryBuilder('s')
            ->join('s.location', 'l') // Assurez-vous que 's' a une relation avec 'Location'
            ->orderBy($sortBy === 'location.name' ? 'l.Name' : 's.' . $sortBy, $order) // Appliquer le tri
            ->getQuery();

        // Exécuter la requête
        $sessions = $query->getResult();

        // Renvoyer la vue avec les sessions triées et les paramètres de tri
        return $this->render('/admin/Formations/formations.html.twig', [
            'sessions' => $sessions,
            'sort' => $sortBy,
            'order' => $order,
        ]);
    }


    #[Route('/formations/{id}', name: 'formations_detail')]
    #[IsGranted('ROLE_ADMIN')]
    public function detailFormation(int $id,Request $request, EntityManagerInterface $entityManager): Response
    {
        // get formation per ID
        $formation = $entityManager->getRepository(Formations::class)->find($id);

        // check if it exist
        if (!$formation) {
            throw $this->createNotFoundException('Formation non trouvée');
        }

        // get associated instructors
        $formateurs = $formation->getInstructor();

        // create form to associate an instructor
        $form = $this->createForm(AssociatedFormateurType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $formateur = $data['formateur'];

            // Associer le formateur à la formation
            $formation->addInstructor($formateur);

            // Save in database
            $entityManager->persist($formation);
            $entityManager->flush();


            $this->addFlash('success', 'Le formateur a été associé avec succès.');

            // Rediriger vers la même page
            return $this->redirectToRoute('formations_detail', ['id' => $formation->getId()]);
        }



        // send back view with formations and associated instructors
        return $this->render('admin/Formations/formation_details.html.twig', [
            'formation' => $formation,
            'formateurs'=> $formateurs,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/formations/{formationId}/formateur/{formateurId}', name: 'formateur_documents')]
    #[IsGranted('ROLE_ADMIN')]
public function documentsParFormateur(int $formationId, int $formateurId, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la formation par ID
        $formation = $entityManager->getRepository(Formations::class)->find($formationId);

        // get Instructors in array
        $formateurs = $formation->getInstructor()->toArray(); // Convertir en tableau

        $formateursUnique = array_unique($formateurs, SORT_REGULAR); // SORT_REGULAR pour comparer les objets



        // Vérifier si la formation existe
        if (!$formation) {
            throw $this->createNotFoundException('Formation non trouvée');
        }

        // Récupérer le formateur par ID
        $formateur = $entityManager->getRepository(User::class)->find($formateurId);

        // Vérifier si le formateur existe
        if (!$formateur) {
            throw $this->createNotFoundException('Formateur non trouvé');
        }

        // Récupérer les documents associés à la formation et au formateur
        $documents = $entityManager->getRepository(Documents::class)->findBy([
            'Formation' => $formation,
            'Formateur' => $formateur,
        ]);

        // Renvoyer la vue avec les documents
        return $this->render('admin/Formations/documents.html.twig', [
            'formation' => $formation,
            'formateur' => $formateur,
            'documents' => $documents,
        ]);
    }
}
