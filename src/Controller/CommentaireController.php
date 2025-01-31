<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaireController extends AbstractController
{
    #[Route('/commentaire', name: 'app_commentaire')]
    public function index(): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'controller_name' => 'commentaireController',
        ]);
    }

    #[Route('/commentaire/generate', name: 'generate_commentaire')]
    public function generateCommentaire(EntityManagerInterface $entityManager): Response
    {
        $commentaire = new commentaire();
        $str_now = date('Y-m-d H:i:s', time());
        $commentaire->setAuteur('Auteur aléatoire');
        $content = file_get_contents('http://loripsum.net/api');
        $commentaire->setContenu(substr($content, 0, 255)); // Adjust 255 to your column size
        $str_now = date('Y-m-d H:i:s', time());
        $commentaire->setDate(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $str_now));

        $article = $entityManager
        ->getRepository(Article::class)
        ->find(19); // Ajustez 10 au nombre d'articles que vous avez

        $commentaire->setArticleId($article);

        // Associer le commentaire à un article existant
        // $article = $entityManager->getRepository(Article::class)->find(1); // Remplacez 1 par l'ID de l'article auquel vous voulez associer le commentaire
        // if ($article) {
        //     $commentaire->setArticle($article);
        // }

        // tell Doctrine you want to (eventually) save the Commentaire (no queries yet)
        $entityManager->persist($commentaire);
        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
        $this->addFlash('success', 'Commentaire avec id : ' );
        return $this->redirectToRoute('article_list');
    }

    #[Route('/commentaire/list', name: 'commentaire_list')]
    public function listcommentaire(EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Commentaire::class);
        $commentaires = $repository->findAll();
        return $this->render('commentaire/list.html.twig', [
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/commentaire/show/{id}', name: 'commentaire_show')]
    public function showcommentaire(int $id, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(commentaire::class);
        $commentaire = $repository->find($id);
        if (!$commentaire) {
            throw $this->createNotFoundException('No commentaire found for id ' . $id);
        }
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }
    #[Route('/commentaire/delete/{id}', name: 'commentaire_delete')]
    public function deleteCommentaire(int $id, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Commentaire::class);
        $commentaire = $repository->find($id);

        if (!$commentaire) {
            throw $this->createNotFoundException('No commentaire found for id ' . $id);
        }

        $entityManager->remove($commentaire);
        $entityManager->flush();

        return new Response('Deleted commentaire with id ' . $id);
    }
}