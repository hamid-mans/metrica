<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\WorkflowRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/', 'app.dashboard.')]
final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(WorkflowRepository $workflowRepository, ProductRepository $productRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'products' => $productRepository->count(),
            'workflows' => $workflowRepository->count(),
        ]);
    }
}
