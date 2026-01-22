<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Step;
use App\Entity\Workflow;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\StepRepository;
use App\Repository\WorkflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/product', 'app.dashboard.product.')]
#[isGranted("ROLE_ADMIN")]
final class ProductController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductRepository $productRepository): Response
    {

        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'create')]
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product, [
            'submit_label' => '<i class="plus icon"></i>Ajouter',
            'submit_class' => 'ui principal button'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit créé avec succès !');

            return $this->redirectToRoute('app.dashboard.product.index');
        }

        return $this->render('product/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/modifier/{id}', name: 'update')]
    public function update(StepRepository $stepRepository, Product $product, WorkflowRepository $workflowRepository, ChartBuilderInterface $builder, EntityManagerInterface $entityManager, Request $request): Response
    {
        $form = $this->createForm(ProductType::class, $product, [
            'submit_label' => '<i class="save icon"></i>Mettre à jour',
            'submit_class' => 'ui principal button'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès !');

            return $this->redirectToRoute('app.dashboard.product.index');
        }

        $colors = [];

        for ($i = 0; $i < 20; $i++) {
            // Répartir les teintes sur le cercle (0-360°)
            $hue = intval(($i * 360 / 20));

            // Saturation faible et luminosité élevée pour pastel
            $saturation = 60; // 50-70% idéal
            $lightness = 80;  // clair, pastel

            $colors[] = "hsl($hue, $saturation%, $lightness%)";
        }


        $productWorkflowChart = $builder->createChart(Chart::TYPE_LINE);

        $datasets = [];
        $highest = 0;
        foreach ($product->getWorkflows() as $workflow) {
            $workflowsTotal[] = $workflow->getPrice();
            if (count($workflow->getSteps()) > $highest) $highest = $workflow->getSteps()->count();

            $datasets[] = [
                'label' => $workflow->getName(),
                'backgroundColor' => 'white',
                'borderColor' => $colors[rand(0, count((array)$colors) - 1)],
                'data' => $workflow->getSteps()->map(function (Step $step) {
                    return $step->getIncome() - $step->getCost();
                })->toArray(),
                'tension' => 0.4
            ];
        }

        for($i = 1; $i <= $highest; $i++) {
            $stepName[] = 'Étape ' . $i;
        }

        $productWorkflowChart->setData([
            'labels' => $stepName,
            'datasets' => $datasets,
        ]);
        $productWorkflowChart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 1000,
                ],
            ],
        ]);

        return $this->render('product/update.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'productWorkflowChart' => $productWorkflowChart,
        ]);
    }

    #[Route('/supprimer/{id}', name: 'delete')]
    public function delete(Product $product, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', 'Produit supprimé avec succès !');

        return $this->redirectToRoute('app.dashboard.product.index');
    }
}
