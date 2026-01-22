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
        $stepName = [];
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





        // CHART 1
        $productWorkflowProfitChart1 = $builder->createChart(Chart::TYPE_LINE);
        $labels1 = [];
        $data1 = [];

        $currentTotal1 = 0;

        $allWorkflows1 = $workflowRepository->findBy(['product' => $product], ['id' => 'ASC']);

        foreach ($allWorkflows1 as $workflow) {
            $workflowTotal1 = 0;
            foreach ($workflow->getSteps() as $step) {

                $income = (float) ($step->getIncome() ?? 0);
                $cost = (float) ($step->getCost() ?? 0);

                $workflowTotal1 += ($income - $cost);
            }

            $currentTotal1 += $workflowTotal1;

            $labels1[] = mb_strimwidth($workflow->getName(), 0, 20, '...');

            $data1[] = round($currentTotal1, 2);
            if ($workflow->isSeparation()) break;
        }

        $productWorkflowProfitChart1->setData([
            'labels' => $labels1,
            'datasets' => [
                [
                    'label' => 'Sous-produit malade',
                    'data' => $data1,
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54,162,235,0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 10,
                ]
            ],
        ]);





        // CHART 2
        $productWorkflowProfitChart2 = $builder->createChart(Chart::TYPE_LINE);
        $labels2 = [];
        $data2 = [];

        $currentTotal2 = 0;

        $allWorkflows2 = $workflowRepository->findBy(['product' => $product], ['id' => 'ASC']);

        foreach ($allWorkflows2 as $workflow) {
            $labels2[] = mb_strimwidth($workflow->getName(), 0, 20, '...');
        }

        foreach ($allWorkflows2 as $workflow) {
            if ($workflow->isSeparation()) continue;
            $workflowTotal2 = 0;
            foreach ($workflow->getSteps() as $step) {

                $income = (float) ($step->getIncome() ?? 0);
                $cost = (float) ($step->getCost() ?? 0);

                $workflowTotal2 += ($income - $cost);
            }

            $currentTotal2 += $workflowTotal2;


            $data2[] = round($currentTotal2, 2);
        }

        $productWorkflowProfitChart2->setData([
            'labels' => $labels2,
            'datasets' => [
                [
                    'label' => 'Sous-produit valorisable',
                    'data' => $data2,
                    'borderColor' => '#77c474',
                    'backgroundColor' => 'rgba(186, 219, 184,0.4)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 10,
                ]
            ],
        ]);


        $productWorkflowProfitChart3 = $builder->createChart(Chart::TYPE_LINE);

        $productWorkflowProfitChart3->setData([
            'labels' => $labels2,
            'datasets' => [
                [
                    'label' => 'Sous-produit malade',
                    'data' => $data1,
                    'borderColor' => '#de6d78',
                    'backgroundColor' => 'rgba(219, 180, 184,0.4)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 10,
                ],
                [
                    'label' => 'Sous-produit valorisable',
                    'data' => $data2,
                    'borderColor' => '#77c474',
                    'backgroundColor' => 'rgba(186, 219, 184,0.4)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 10,
                ]
            ],
        ]);


        return $this->render('product/update.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'productWorkflowChart' => $productWorkflowChart,
            'productWorkflowProfitChart1' => $productWorkflowProfitChart1,
            'productWorkflowProfitChart2' => $productWorkflowProfitChart2,
            'productWorkflowProfitChart3' => $productWorkflowProfitChart3,
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

    #[Route('/clone/{id}', name: 'clone')]
    public function clone(Product $product, EntityManagerInterface $entityManager): Response
    {
        $originalProduct = $product;
        $newProduct = new Product();

        $newProduct->setName($originalProduct->getName() . ' (copie)');

        foreach ($originalProduct->getWorkflows() as $workflow) {

            $newWorkflow = new Workflow();
            $newWorkflow->setName($workflow->getName());
            $newWorkflow->setSeparation($workflow->isSeparation());
            $newWorkflow->setProduct($newProduct);

            foreach ($workflow->getSteps() as $step) {

                $newStep = new Step();

                $newStep->setName($step->getName());
                $newStep->setCost($step->getCost());
                $newStep->setIncome($step->getIncome());
                $newStep->setStepNumber($step->getStepNumber());

                $newStep->setWorkflow($newWorkflow);
                $newWorkflow->addStep($newStep);
            }

            $newProduct->addWorkflow($newWorkflow);
        }

        $entityManager->persist($newProduct);
        $entityManager->flush();

        return $this->redirectToRoute('app.dashboard.product.index');
    }
}
