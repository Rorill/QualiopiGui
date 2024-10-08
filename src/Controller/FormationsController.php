<?php

namespace App\Controller;

use App\Entity\Documents;
use App\Entity\Location;
use App\Entity\User;
use App\Entity\Formations;
use App\Form\AssociatedFormateurType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
class FormationsController extends AbstractController
{
    #[Route('/formations', name: 'app_formations')]
    #[IsGranted('ROLE_ADMIN')]
    public function listFormations(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request):Response
    {
        // Récupérer les paramètres de filtrage
        $city = $request->query->get('City');
        $startingDate = $request->query->get('starting_date');

        // Récupérer les villes pour le champ select
        $cities = $entityManager->getRepository(Location::class)->findAll();

        // Récupérer les paramètres de tri et de l'ordre
        $sort = $request->query->get('sort', 'starting_date'); // Trier par startingDate par défaut
        $order = $request->query->get('order', 'asc');        // Ordre ascendant par défaut
        $limit = $request->query->get('limit', 20);           // Par défaut, afficher 20 éléments

        // Construire la requête pour le tri et les filtres
        $queryBuilder = $entityManager->getRepository(Formations::class)->createQueryBuilder('f');

        // Filtrage par city
        if ($city) {
            $queryBuilder->leftJoin('f.location', 'l')
                ->andWhere('l.city = :city')
                ->setParameter('city', $city);
        }

        // Filtrage par startingDate
        if ($startingDate) {
            $queryBuilder->andWhere('f.startingDate >= :startingDate')
                ->setParameter('startingDate', new \DateTime($startingDate));
        }

        // Vérification du tri
        if ($sort == 'location.city') {
            $queryBuilder->orderBy('l.city', $order);
        } else {
            $queryBuilder->orderBy('f.' . $sort, $order);
        }

        // Récupérer la requête
        $query = $queryBuilder->getQuery();

        // Paginer les résultats
        $sessions = $paginator->paginate(
            $query, // La requête à paginer
            $request->query->getInt('page', 1), // Numéro de page
            $limit // Nombre de résultats par page
        );

        return $this->render('admin/Formations/formations.html.twig', [
            'sessions' => $sessions,
            'sort' => $sort,
            'order' => $order,
            'limit' => $limit,
            'city' => $city,
            'startingDate' => $startingDate,
            'cities' => $cities, // Passer les villes à la vue
        ]);
    }




#[Route('/formations/{id}', name: 'formations_detail')]
    #[IsGranted('ROLE_ADMIN')]
    public function detailFormation(Request $request, EntityManagerInterface $entityManager, $id): Response
    {  // Récupérer la formation par son ID
        $formation = $entityManager->getRepository(Formations::class)->find($id);

        // Récupérer les documents associés à cette formation, triés par formateur
        $documents = $entityManager->getRepository(Documents::class)->findBy(['Formation' => $formation], ['Formateur' => 'ASC']);

        // Récupérer les formateurs associés aux documents
        $formateurs = [];
        foreach ($documents as $document) {
            $formateur = $document->getFormateur(); // Assurez-vous que cette méthode existe dans votre entité Document
            if ($formateur) {
                $formateurs[$formateur->getId()] = $formateur; // Utiliser l'ID comme clé pour éviter les doublons
            }
        }

        $form = $this->createForm(AssociatedFormateurType::class); // Remplacez par le bon type de formulaire

        // Gérer le formulaire
        $form->handleRequest($request );
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $formateur = $data['formateur']; // Obtenir le formateur sélectionné

            // Ajouter la logique pour associer le formateur à la formation
            $formation->addFormateur($formateur); // Assurez-vous que cette méthode existe dans l'entité Formation
            $entityManager->persist($formation);
            $entityManager->flush();

            // Rediriger ou ajouter un message de succès
            return $this->redirectToRoute('formations_detail', ['id' => $formation->getId()]);
        }


        return $this->render('admin/Formations/formation_details.html.twig', [
            'formation' => $formation,
            'documents' => $documents,
            'formateurs' => $formateurs,
            'form' => $form->createView(), // Passer le formulaire à la vue
        ]);
        }




}
