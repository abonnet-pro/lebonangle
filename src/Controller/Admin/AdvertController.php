<?php

namespace App\Controller\Admin;

use App\Entity\Advert;
use App\Repository\AdvertRepository;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/admin/advert')]
class AdvertController extends AbstractController
{
    #[Route('/', name: 'admin_advert_index', methods: ['GET'])]
    public function index(PaginatorInterface $paginator, Request $request, AdvertRepository $advertRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $queryBuilder = $advertRepository->findBy(
            [],
            ['id' => 'DESC']
        );
        $adverts = $paginator->paginate($queryBuilder, $page, 30);

        return $this->render('admin/advert/index.html.twig', [
            'adverts' => $adverts
        ]);
    }

    #[Route('/{id}', name: 'admin_advert_show', methods: ['GET'])]
    public function show(Advert $advert): Response
    {
        return $this->render('admin/advert/show.html.twig', [
            'advert' => $advert,
            'pictures' => $advert->getPictures(),
        ]);
    }

    #[Route(path: '/{id}/{to}', name: 'admin_advert_transition', methods: ['GET'])]
    public function applyTransition(WorkflowInterface $advertStateMachine, EntityManagerInterface $manager, Advert $advert, string $to): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $advertStateMachine->apply($advert, $to);
        $manager->flush();

        return $this->redirectToRoute('admin_advert_index');
    }
}
