<?php

namespace App\Controller;
use App\Entity\Documents;
use App\Entity\Formations;
use App\Entity\User;
use App\Form\CsvImportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

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
                    $starting_date = new \DateTime($data[1]);
                    $ending_date = new \DateTime($data[2]);
                    $location = $data[3];

                    // check if the formation already exist
                    $existingSession = $entityManager->getRepository(Formations::class)
                        ->findOneBy(['name' => $name, 'starting_date' => $starting_date, 'ending_date' => $ending_date]);

                    if (!$existingSession) {
                        // Create if it doesn't
                        $session = new Formations();
                        $session->setName($name);
                        $session->setStartingDate($starting_date);
                        $session->setEndingDate($ending_date);
                        $session->setSite($location);

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




}
