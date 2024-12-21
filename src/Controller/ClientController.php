<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ClientController extends AbstractController
{
    #[Route('/clients', name: 'client_index')]
public function index(Request $request, PaginatorInterface $paginator, ClientRepository $clientRepository): Response
{
    // Recherche et filtre
    $search = $request->query->get('search', '');
    $hasAccount = $request->query->get('hasAccount', null); // Récupération du filtre

    // Requête avec jointure explicite
    $queryBuilder = $clientRepository->createQueryBuilder('c')
        ->leftJoin('c.utilisateur', 'u') // Jointure explicite sur "utilisateur"
        ->addSelect('u'); // Ajouter la jointure à la requête

    if ($search) {
        $queryBuilder->andWhere('c.telephone LIKE :search')
            ->setParameter('search', '%' . $search . '%');
    }

    if ($hasAccount !== null && $hasAccount !== '') {
        if ($hasAccount == '1') {
            $queryBuilder->andWhere('u IS NOT NULL'); // Clients ayant un compte
        } elseif ($hasAccount == '0') {
            $queryBuilder->andWhere('u IS NULL'); // Clients sans compte
        }
    }

    $pagination = $paginator->paginate(
        $queryBuilder->getQuery(),
        $request->query->getInt('page', 1), // Page actuelle
        4 // Limite par page
    );

    return $this->render('client/index.html.twig', [
        'pagination' => $pagination,
        'search' => $search,
        'hasAccount' => $hasAccount,
    ]);
}

    #[Route('/clients/new', name: 'client_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si "Créer un compte" est coché
            if ($form->get('createAccount')->getData()) {
                $user = new User();
                $user->setEmail($form->get('email')->getData());
                $user->setPassword($form->get('password')->getData()); // Utilisation du mot de passe saisi
                $user->setRoles(['ROLE_CLIENT']); // Rôle "ROLE_CLIENT"
                $user->setClient($client);
                $entityManager->persist($user);
            }

            $entityManager->persist($client);
            $entityManager->flush();

            $this->addFlash('success', 'Client ajouté avec succès.');

            return $this->redirectToRoute('client_index');
        }

        return $this->render('client/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/clients/{id}', name: 'client_details')]
public function details(int $id, Request $request, ClientRepository $clientRepository): Response
{
    // Récupération du client
    $client = $clientRepository->find($id);

    if (!$client) {
        throw $this->createNotFoundException('Client non trouvé');
    }

    // Initialisation de `$filteredDettes` avec toutes les dettes par défaut
    $dettes = $client->getDettes();
    $filteredDettes = $dettes;

    // Récupérer les paramètres de filtre
    $filter = $request->query->get('filter', null);
    $specificDate = $request->query->get('specific_date', null);

    // Filtrer par soldées ou non soldées
    if ($filter === 'soldes') {
        $filteredDettes = $dettes->filter(function ($dette) {
            // Vérifie si le montant restant est exactement 0 ou null
            return $dette->getMontantRestant() === null || $dette->getMontantRestant() == 0;
        });
    } elseif ($filter === 'non-soldes') {
        $filteredDettes = $dettes->filter(fn($dette) => $dette->getMontantRestant() > 0);
    }

    // Filtrer par une date spécifique
    if ($specificDate) {
        $specificDate = new \DateTime($specificDate);
        $filteredDettes = $filteredDettes->filter(fn($dette) =>
            $dette->getDate()->format('Y-m-d') === $specificDate->format('Y-m-d')
        );
    }

    return $this->render('client/details.html.twig', [
        'client' => $client,
        'filteredDettes' => $filteredDettes,
        'specificDate' => $specificDate,
    ]);
}

}
