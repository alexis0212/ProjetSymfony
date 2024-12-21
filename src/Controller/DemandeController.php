<?php

namespace App\Controller;

use App\Entity\Dette;
use App\Repository\DetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemandeController extends AbstractController
{
    #[Route('/demandes', name: 'demande_index')]
    public function index(
        Request $request,
        DetteRepository $detteRepository,
        PaginatorInterface $paginator
    ): Response {
        $etat = $request->query->get('etat', 'en_cours'); // Filtre par défaut

        $queryBuilder = $detteRepository->createQueryBuilder('d')
            ->join('d.client', 'c') // Jointure pour récupérer les informations du client
            ->addSelect('c')
            ->andWhere('d.etat = :etat')
            ->setParameter('etat', $etat);

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('demande/index.html.twig', [
            'demandes' => $pagination,
            'etat' => $etat,
        ]);
    }

    #[Route('/demandes/{id}/details', name: 'demandes_details')]
    public function details(EntityManagerInterface $entityManager, int $id): Response
    {
        $dette = $entityManager->getRepository(Dette::class)->find($id);

        if (!$dette) {
            throw $this->createNotFoundException('Cette demande n\'existe pas.');
        }

        return $this->render('demande/details.html.twig', [
            'dette' => $dette,
            'articles' => $dette->getArticles(),
        ]);
    }

    #[Route('/demandes/{id}/accepter', name: 'demandes_accepter')]
    public function accepter(Dette $dette, EntityManagerInterface $entityManager): Response
    {
        $dette->setEtat('accepte');
        $entityManager->flush();

        $this->addFlash('success', 'La demande a été acceptée.');
        return $this->redirectToRoute('demande_index', ['etat' => 'en_cours']);
    }

    #[Route('/demandes/{id}/refuser', name: 'demandes_refuser')]
    public function refuser(Dette $dette, EntityManagerInterface $entityManager): Response
    {
        $dette->setEtat('refuse');
        $entityManager->flush();

        $this->addFlash('error', 'La demande a été refusée.');
        return $this->redirectToRoute('demande_index', ['etat' => 'en_cours']);
    }

    #[Route('/demandes/{id}/relancer', name: 'demandes_relancer')]
    public function relancer(Dette $dette, EntityManagerInterface $entityManager): Response
    {
        if ($dette->getEtat() === 'refuse') {
            $dette->setEtat('en_cours'); // Changer l'état à "en_cours"
            $entityManager->flush();
            $this->addFlash('success', 'La demande a été relancée avec succès.');
        }
    
        return $this->redirectToRoute('demande_index', ['etat' => 'en_cours']);
    }
    
}
