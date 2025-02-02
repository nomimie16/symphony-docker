<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER', statusCode: 403, message: 'Vous devez être connecté et avoir le rôle d\'administrateur.')]


final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_SUPER_ADMIN', statusCode: 403, message: 'Accès refusé.')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }



    #[Route('/admin/test1', name: 'app_admin_test1')]
    public function index_test(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // or add an optional message - seen by developers
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a
        page without having ROLE_ADMIN');
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/test2', name: 'app_admin_test2')]
    #[IsGranted('ROLE_SUPER_ADMIN', statusCode: 403, message: 'Vous n\'êtes pas autorisé à acceder aux utilisateurs.')]
    public function index_test2(): Response
        {
        return $this->render('admin/superadmin.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/userslist', name: 'app_admin_users')]
    #[IsGranted('ROLE_SUPER_ADMIN', statusCode: 403, message: 'Vous n\'avez pas la permission de voir la liste des utilisateurst.')]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {

        $repository = $entityManager->getRepository(User::class);
        $users = $repository->findAll();
        return $this->render('admin/userslist.html.twig', [
            'users' => $users,
        ]);
        
    }

    #[Route('/admin/show/{id}', name: 'user_show')]
    #[IsGranted('ROLE_SUPER_ADMIN', statusCode: 403, message: 'You do not have permission to view the user list.')]

    public function showUser(int $id, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(User::class);
        $user = $repository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('No user found for id ' . $id);
        }

        $this->addFlash('success', 'user chargé !');
        return $this->render('admin/show.html.twig', [
            'user' => $user,
        ]);

    }

    #[Route('/admin/edit/{id}', name: 'update_user_role')]
    #[IsGranted('ROLE_SUPER_ADMIN', statusCode: 403, message: 'You do not have permission to modify user roles.')]
    public function editUserRole(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $repository = $entityManager->getRepository(User::class);
        $user = $repository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('No user found for id ' . $id);
        }

        $newRole = $request->request->get('role');
        if (!$newRole) {
            throw $this->createNotFoundException('No role provided');
        }

        $user->setRoles([$newRole]);
        $entityManager->flush();

        $this->addFlash('success', 'User role updated successfully!');
        return $this->redirectToRoute('app_admin_users');
    }
}