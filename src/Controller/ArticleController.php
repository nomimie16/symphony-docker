<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use Symfony\Component\Security\Http\Attribute\IsGranted;


use App\Form\ArticleType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article/generate', name: 'generate_article')]
    public function generateArticle(EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $str_now = date('Y-m-d H:i:s', time());
        $article->setTitre('Titre aléatoire #' . $str_now);
        $content = file_get_contents('http://loripsum.net/api');
        $article->setTexte(substr($content, 0, 255)); 
        $article-> setPublie(true);
        $article->setDate(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $str_now));

        // tell Doctrine you want to (eventually) save the Article (no queries yet)
        $entityManager->persist($article);
        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
        $this->addFlash('success', 'Article généré avec id : '. $article->getId() );
        return $this->redirectToRoute('article_list');
        
    }

    #[Route('/article/list', name: 'article_list')]
    public function listArticle(EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Article::class);
        $articles = $repository->findAll();
        return $this->render('article/list.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/article/show/{id}', name: 'article_show')]
    public function showArticle(int $id, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Article::class);
        $article = $repository->find($id);
        if (!$article) {
            throw $this->createNotFoundException('No article found for id ' . $id);
        }

        // Assuming you have a Comment entity related to the Article entity
        $commentRepository = $entityManager->getRepository(Commentaire::class);
        $commentaires = $commentRepository->findBy(['article_id' => $id]);

        $this->addFlash('success', 'Article chargé !');
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/article/new', name: 'article_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
        {
            $article = new Article();
            $article->setTitre('Which Title ?');
            $article->setTexte('And which content ?');
            $now = time();
            $str_now = date('Y-m-d H:i:s', $now);
            $article->setDate(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $str_now));
            $form = $this->createForm(ArticleType::class, $article);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($article);
                $entityManager->flush();
                $this->addFlash('success', 'Article créé !');
                return $this->redirectToRoute('article_list');
            }

            return $this->render('article/new.html.twig', [
                'form' => $form->createView(),
                'article' => $article,
            ]);
        }
    
    #[Route('/article/edit/{id}', name: 'article_edit')]

    public function editArticle(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Article::class);
        $article = $repository->find($id);
        if (!$article) {
            throw $this->createNotFoundException('No article found for id ' . $id);
        }

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Article modifié !');
            return $this->redirectToRoute('article_list');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }


    #[Route('/article/delete/{id}', name: 'article_delete')]
    #[IsGranted('ROLE_SUPER_ADMIN', statusCode: 403, message: 'Vous n\'avez pas la permission de supprimer.')]
    #[IsGranted('ROLE_ARTICLE_ADMIN', statusCode: 403, message: 'Vous n\'avez pas la permission de supprimer.')]

    public function deleteArticle(int $id, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Article::class);
        $article = $repository->find($id);
        if (!$article) {
            throw $this->createNotFoundException('No article found for id ' . $id);
        }

        // Remove associated comments
        $commentRepository = $entityManager->getRepository(Commentaire::class);
        $commentaires = $commentRepository->findBy(['article_id' => $id]);
        foreach ($commentaires as $commentaire) {
            $entityManager->remove($commentaire);
        }

        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('delete', 'Article supprimé !');
        return $this->redirectToRoute('article_list');
    }
}