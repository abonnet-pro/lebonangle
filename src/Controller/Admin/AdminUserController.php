<?php

namespace App\Controller\Admin;

use App\Entity\AdminUser;
use App\Form\AdminUserType;
use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin/user')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'admin_user_index', methods: ['GET'])]
    public function index(AdminUserRepository $adminUserRepository): Response
    {
        return $this->render('admin/admin_user/index.html.twig', [
            'admin_users' => $adminUserRepository->findBy(
                [],
                ['id' => 'DESC']
            ),
        ]);
    }

    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $adminUser = new AdminUser();
        $form = $this->createForm(AdminUserType::class, $adminUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($adminUser);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->clear();
            $this->get('session')->getFlashBag()->add(
                'save_admin',
                'Admin saved'
            );

            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_user/new.html.twig', [
            'admin_user' => $adminUser,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(AdminUser $adminUser): Response
    {
        return $this->render('admin/admin_user/show.html.twig', [
            'admin_user' => $adminUser,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AdminUser $adminUser, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminUserType::class, $adminUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_user/edit.html.twig', [
            'admin_user' => $adminUser,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(AdminUserRepository $adminUserRepository, TokenStorageInterface $tokenStorage, Request $request, AdminUser $adminUser, EntityManagerInterface $entityManager): Response
    {
        $canDelete = $this->canDeleteAdmin($adminUserRepository, $adminUser, $tokenStorage);

        if ($this->isCsrfTokenValid('delete'.$adminUser->getId(), $request->request->get('_token')) && $canDelete) {
            $entityManager->remove($adminUser);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->clear();
            $this->get('session')->getFlashBag()->add(
                'delete_admin_ok',
                'Admin deleted'
            );
        }

        return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    private function canDeleteAdmin(AdminUserRepository $adminUserRepository, AdminUser $adminUser, TokenStorageInterface $tokenStorage): bool
    {
        $user = $tokenStorage->getToken()->getUser();

        if($user->getUserIdentifier() === $adminUser->getUserIdentifier() || $adminUserRepository->getCountAdmin() <= 1)
        {
            $this->get('session')->getFlashBag()->clear();
            $this->get('session')->getFlashBag()->add(
                'delete_admin_ko',
                'Cannot delete admin'
            );

            return false;
        }

        return true;
    }
}
