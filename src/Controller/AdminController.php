<?php

namespace App\Controller;
use App\Entity\Documents;
use App\Entity\Formations;
use App\Entity\Location;
use App\Entity\User;
use App\Form\CsvImportType;
use App\Form\LocationType;
use App\Form\SessionType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(EntityManagerInterface $entityManager,UserRepository $userRepository): Response
    {// get info in entities
        $formations = $entityManager->getRepository(Formations::class)->findAll();
        $formateurs = $entityManager->getRepository(User::class)->findall();
        $documents = $entityManager->getRepository(Documents::class)->findAll();
        $fullname = $userRepository->findUsersWithFullName();
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
            'formations' => $formations,
            'formateurs' => $formateurs,
            'documents' => $documents,
        ]);
    }

    #[Route('/admin/import-csv', name: 'admin_import_csv')]
    #[IsGranted('ROLE_ADMIN')]
    public function importCsv(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csv_file')->getData();

            // check if file is valid
            if ($csvFile) {
                $file = fopen($csvFile->getPathname(), 'r');

                // Reading CSV file line by line
                $rowCount = 0;
                $lineCount = 0;
                while (($data = fgetcsv($file, 1000, ";")) !== FALSE) {

                    $lineCount++;

                    // Ignorer la première ligne (les titres des colonnes)
                    if ($lineCount == 1) {
                        continue;
                    }




                    // CSV file has 4 columns
                    $name = $data[0];
                    $startDateStr = trim($data[1] ?? null);
                    $endDateStr = trim($data[2] ?? null);
                    $locationName = $data[3];

                    $startDate = \DateTime::createFromFormat('d/m/Y', $startDateStr);
                    $endDate = \DateTime::createFromFormat('d/m/Y', $endDateStr);

                    // Rechercher l'entité Location par nom
                    $location = $entityManager->getRepository(Location::class)->findOneBy(['Name' => $locationName]);

                    if (!$location) {
                        continue; // Ignorer cette ligne si le lieu n'est pas trouvé
                    }



                    // check if the formation already exist
                    $existingSession = $entityManager->getRepository(Formations::class)
                        ->findOneBy(['name' => $name, 'starting_date' => $startDate, 'ending_date' => $endDate]);

                    if (!$existingSession) {
                        // Create if it doesn't
                        $session = new Formations();
                        $session->setName($name);
                        $session->setStartingDate($startDate);
                        $session->setEndingDate($endDate);
                        $session->setLocation($location); // Assigner l'objet Location trouvé

                        $entityManager->persist($session);
                        $rowCount++;
                    }
                }

                fclose($file);
                $entityManager->flush();

                $this->addFlash('success', $rowCount . ' formations ont été importées avec succès, les doublons ont été ignorés.');
                return $this->redirectToRoute('app_admin');
            }
        }

        return $this->render('admin/import_csv.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    //route to add User

    #[Route('/admin/user/add', name: 'admin_user_add')]
    #[IsGranted('ROLE_ADMIN')]
    public function addUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // password hash
            $user->setPassword(
                $passwordHasher->hashPassword($user, $user->getPassword())
            );
            $user->setRoles(['ROLE_USER']);  // Default role


            // Register user in db
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/app_adduser.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // function to delete user

    #[Route('/admin/user/delete/{id}', name: 'admin_user_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('app_admin');
    }

    // function to modify user

    #[Route('/admin/user/edit/{id}', name: 'admin_user_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function editUser(int $id, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hacher le mot de passe si modifié
            if ($form->get('password')->getData()) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $user->getPassword())
                );
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/edit_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    // function to add Formation

    #[Route('/admin/session/add', name: 'admin_session_add')]
    #[IsGranted('ROLE_ADMIN')]
    public function addSession(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = new Formations();
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Register formation in database
            $entityManager->persist($session);
            $entityManager->flush();

            $this->addFlash('success', 'Formation ajoutée avec succès.');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/add_session.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //function to edit session
    #[Route('/admin/session/edit/{id}', name: 'admin_session_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function editSession(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $entityManager->getRepository(Formations::class)->find($id);

        if (!$session) {
            throw $this->createNotFoundException('Formation non trouvée.');
        }

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Formation modifiée avec succès.');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/edit_session.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // function to delete Formation

    #[Route('/admin/session/delete/{id}', name: 'admin_session_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteSession(int $id, EntityManagerInterface $entityManager): Response
    {
        $session = $entityManager->getRepository(Formations::class)->find($id);

        if (!$session) {
            throw $this->createNotFoundException('Formation non trouvée.');
        }

        $entityManager->remove($session);
        $entityManager->flush();

        $this->addFlash('success', 'Formation supprimée avec succès.');
        return $this->redirectToRoute('app_admin');
    }

    // view Uploaded documents

    #[Route('/admin/documents', name: 'admin_documents')]
    #[IsGranted('ROLE_ADMIN')]
    public function viewDocuments(EntityManagerInterface $entityManager): Response
    {
        // get all Formations with their associated docs
        $sessions = $entityManager->getRepository(Formations::class)->findAll();

        return $this->render('admin/documents.html.twig', [
            'sessions' => $sessions,
        ]);
    }

    // add Location
    #[Route('/admin/location/add', name: 'admin_location_add')]
    #[IsGranted('ROLE_ADMIN')]
    public function addLocation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($location);
            $entityManager->flush();

            $this->addFlash('success', 'Lieu ajouté avec succès.');
            return $this->redirectToRoute('admin_location_list');
        }

        return $this->render('Location/add_location.html.twig', [
            'form' => $form->createView(),
        ]);
    }


        // modify a Location

        #[Route('/admin/location/edit/{id}', name: 'admin_location_edit')]
        #[IsGranted('ROLE_ADMIN')]
        public function editLocation(int $id, Request $request, EntityManagerInterface $entityManager): Response
        {
            $location = $entityManager->getRepository(Location::class)->find($id);

            if (!$location) {
                throw $this->createNotFoundException('Lieu non trouvé.');
            }

            $form = $this->createForm(LocationType::class, $location);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Lieu modifié avec succès.');
                return $this->redirectToRoute('admin_location_list');
            }

            return $this->render('Location/edit_location.html.twig', [
                'form' => $form->createView(),
            ]);






    }

    // supress a Location
    #[Route('/admin/location/delete/{id}', name: 'admin_location_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteLocation(int $id, EntityManagerInterface $entityManager): Response
    {
        $location = $entityManager->getRepository(Location::class)->find($id);

        if (!$location) {
            throw $this->createNotFoundException('Lieu non trouvé.');
        }

        $entityManager->remove($location);
        $entityManager->flush();

        $this->addFlash('success', 'Lieu supprimé avec succès.');
        return $this->redirectToRoute('admin_location_list');
    }

    // list location

    #[Route('/admin/location/list', name: 'admin_location_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function listLocations(EntityManagerInterface $entityManager): Response
    {
        $locations = $entityManager->getRepository(Location::class)->findAll();

        return $this->render('Location/list_location.html.twig', [
            'locations' => $locations,
        ]);
    }




}
