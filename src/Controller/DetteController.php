<?php

namespace App\Controller;

use App\Entity\Dette;
use App\Entity\Paiement;
use App\Entity\Client; // Import de la classe Client
use App\Form\DetteType;
use App\Form\PaiementType;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DetteController extends AbstractController
{
    #[Route('/dettes', name: 'dette_index')]
    public function index(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager): Response
    {
        $queryBuilder = $entityManager->getRepository(Dette::class)->createQueryBuilder('d');
    
        // Filtrage par client
        $clientId = $request->query->get('client');
        if ($clientId) {
            $queryBuilder->andWhere('d.client = :client')
                ->setParameter('client', $clientId);
        }
    
        // Filtrage par date
       // Filtrage par date
$specificDate = $request->query->get('specific_date');
if ($specificDate) {
    // Convertir la date fournie au format d-m-Y vers un objet DateTime
    $specificDate = \DateTime::createFromFormat('d-m-Y', $specificDate);
    if ($specificDate) {
        // Définir les bornes de la journée
        $startOfDay = $specificDate->setTime(0, 0, 0);
        $endOfDay = (clone $specificDate)->setTime(23, 59, 59);
        
        // Utiliser BETWEEN pour filtrer les dettes entre les bornes
        $queryBuilder->andWhere('d.date BETWEEN :startOfDay AND :endOfDay')
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay);
    }
}

    
        // Filtrage par statut
        $status = $request->query->get('status');
        if ($status === 'soldes') {
            $queryBuilder->andWhere('d.montantRestant <= 0');
        } elseif ($status === 'non-soldes') {
            $queryBuilder->andWhere('d.montantRestant > 0');
        }
    
        // Pagination
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            5
        );
    
        return $this->render('dette/index.html.twig', [
            'pagination' => $pagination,
            'clients' => $entityManager->getRepository(Client::class)->findAll(),
        ]);
    }
    


    #[Route('/dette/new', name: 'dette_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dette = new Dette();
        $form = $this->createForm(DetteType::class, $dette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Calcul du montant restant
            $dette->setMontantRestant($dette->getMontant() - $dette->getMontantVerser());

            $entityManager->persist($dette);
            $entityManager->flush();

            $this->addFlash('success', 'La dette a été créée avec succès.');

            return $this->redirectToRoute('dette_index');
        }

        return $this->render('dette/newDette.html.twig', [
            'form' => $form->createView(),
        ]);
    }

  #[Route('/dette/{id}', name: 'dette_details')]
    public function details(Dette $dette, Request $request, PaginatorInterface $paginator): Response
    {
        // Récupération des paiements de la dette
        $paiementsQuery = $dette->getPaiement();

        // Pagination des paiements
        $pagination = $paginator->paginate(
            $paiementsQuery, // Query de paiements
            $request->query->getInt('page', 1), // Page actuelle
            2 // Nombre de paiements par page
        );

        return $this->render('dette/details.html.twig', [
            'dette' => $dette,
            'paiements' => $pagination,
            'articles' => $dette->getArticles(),
        ]);
    }

 #[Route('/dette/{id}/paiement/new', name: 'paiement_new')]
    public function newPaiement(Dette $dette, Request $request, EntityManagerInterface $entityManager): Response
    {
        $paiement = new Paiement();
        $paiement->setDette($dette);
        $paiement->setClient($dette->getClient()); // Associer le client automatiquement

        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour les montants de la dette
            $dette->setMontantVerser($dette->getMontantVerser() + $paiement->getMontant());
            $dette->setMontantRestant($dette->getMontant() - $dette->getMontantVerser());

            $entityManager->persist($paiement);
            $entityManager->flush();

            return $this->redirectToRoute('dette_details', ['id' => $dette->getId()]);
        }

        return $this->render('paiement/new.html.twig', [
            'form' => $form->createView(),
            'dette' => $dette,
        ]);
    }

}

