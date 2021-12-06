<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\AdvertRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'admin_category_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator, CategoryRepository $categoryRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $queryBuilder = $categoryRepository->findBy(
            [],
            ['id' => 'DESC']
        );
        $categories = $paginator->paginate($queryBuilder, $page, 30);

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'admin_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->clear();
            $this->get('session')->getFlashBag()->add(
                'save_category',
                'Category saved'
            );

            return $this->redirectToRoute('admin_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->get('session')->getFlashBag()->clear();
            $this->get('session')->getFlashBag()->add(
                'save_category',
                'Category saved'
            );

            return $this->redirectToRoute('admin_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(AdvertRepository $advertRepository, Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $canDelete = $this->canDeleteCategory($advertRepository, $category);

        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token')) && $canDelete) {
            $entityManager->remove($category);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->clear();
            $this->get('session')->getFlashBag()->add(
                'delete_category_ok',
                'Category deleted'
            );
        }

        return $this->redirectToRoute('admin_category_index', [], Response::HTTP_SEE_OTHER);
    }

    private function canDeleteCategory(AdvertRepository $advertRepository, Category $category): bool
    {
        $count = $advertRepository->count(
            ['category' => $category]
        );

        if($count >= 1)
        {
            $this->get('session')->getFlashBag()->clear();
            $this->get('session')->getFlashBag()->add(
                'delete_category_ko',
                'Cannot delete category'
            );

            return false;
        }

        return true;
    }
}
