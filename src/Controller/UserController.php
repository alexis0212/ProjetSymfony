<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_index')]
    public function index(
        UserRepository $userRepository, 
        Request $request, 
        PaginatorInterface $paginator
    ): Response {
        $queryBuilder = $userRepository->createQueryBuilder('u');
    
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(), // Requête
            $request->query->getInt('page', 1), // Page courante
            5 // Nombre d'éléments par page
        );
    
        return $this->render('user/index.html.twig', [
            'pagination' => $pagination,
            'selectedRole' => 'all', // Par défaut, rôle "all" sélectionné
        ]);
    }
    


    #[Route('/users/new', name: 'user_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}/activate', name: 'user_activate')]
public function activate(User $user, EntityManagerInterface $entityManager): Response
{
    // Utilisation correcte de la méthode isActive()
    $user->setIsActive(!$user->isActive());
    $entityManager->flush();

    return $this->redirectToRoute('user_index');
}


#[Route('/users/filter/{role}', name: 'user_filter', defaults: ['role' => 'all'])]
public function filterByRole(
    UserRepository $userRepository, 
    string $role, 
    Request $request, 
    PaginatorInterface $paginator
): Response {
    $queryBuilder = $userRepository->createQueryBuilder('u');

    if ($role !== 'all') {
        $queryBuilder->andWhere('u.roles LIKE :role')
                     ->setParameter('role', '%"ROLE_' . strtoupper($role) . '"%');
    }

    $pagination = $paginator->paginate(
        $queryBuilder->getQuery(),
        $request->query->getInt('page', 1),
        5
    );

    return $this->render('user/index.html.twig', [
        'pagination' => $pagination,
        'selectedRole' => $role, // Rôle filtré
    ]);
}

}
