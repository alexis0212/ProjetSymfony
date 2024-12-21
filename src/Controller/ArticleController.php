<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/articles', name: 'article_index')]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        $filter = $request->query->get('filter', 'all');

        $queryBuilder = $entityManager->getRepository(Article::class)->createQueryBuilder('a');
        if ($filter === 'dis') {
            $queryBuilder->andWhere('a.qteStock > 0');
        } elseif ($filter === 'rup') {
            $queryBuilder->andWhere('a.qteStock = 0');
        }

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('article/index.html.twig', [
            'articles' => $pagination,
            'filter' => $filter,
        ]);
    }

    #[Route('/articles/update', name: 'article_save', methods: ['POST'])]
    public function saveQuantity(Request $request, EntityManagerInterface $entityManager): Response
    {
        $articleId = $request->request->get('articleId');
        $newQuantity = (int)$request->request->get('newQuantity');

        $article = $entityManager->getRepository(Article::class)->find($articleId);

        if ($article && $newQuantity >= 0) {
            $article->setQteStock($newQuantity);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_index');
    }

    #[Route('/articles/new', name: 'article_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }



}
