<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\WorkflowRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/', 'app.dashboard.')]
final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProductRepository $productRepository, WorkflowRepository $workflowRepository, ChartBuilderInterface $builder): Response
    {

        $products = $productRepository->findAll();

        $labels1 = [];
        $profits1 = [];
        $colors1 = [];

        foreach ($products as $product) {
            $totalProfit1 = 0;

            foreach ($product->getWorkflows() as $workflow) {
                foreach ($workflow->getSteps() as $step) {
                    $income = (float) ($step->getIncome() ?? 0);
                    $cost = (float) ($step->getCost() ?? 0);
                    $totalProfit1 += ($income - $cost);
                }

                if ($workflow->isSeparation()) break;
            }

            $labels1[] = mb_strimwidth($product->getName(), 0, 20, '...');
            $profits1[] = round($totalProfit1, 2);

            // Couleur pastel aléatoire
            $hue1 = rand(0, 360);
            $colors1[] = "hsl($hue1, 60%, 75%)";
        }



        // Partie 2
        $labels2 = [];
        $profits2 = [];
        $colors2 = [];

        foreach ($products as $product) {
            $totalProfit2 = 0;

            foreach ($product->getWorkflows() as $workflow) {
                if ($workflow->isSeparation()) continue;
                foreach ($workflow->getSteps() as $step) {
                    $income = (float) ($step->getIncome() ?? 0);
                    $cost = (float) ($step->getCost() ?? 0);
                    $totalProfit2 += ($income - $cost);
                }
            }

            $labels2[] = mb_strimwidth($product->getName(), 0, 20, '...');
            $profits2[] = round($totalProfit2, 2);

            // Couleur pastel aléatoire
            $hue2 = rand(0, 360);
            $colors2[] = "hsl($hue2, 60%, 75%)";
        }






        $productProfitChart = $builder->createChart(Chart::TYPE_BAR);
        $productProfitChart->setData([
            'labels' => $labels1,
            'datasets' => [
                [
                    'label' => 'Sous-produit malade',
                    'data' => $profits1,
                    'backgroundColor' => '#f5abab',
                    'borderColor' => '#555',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Sous-produit valorisable',
                    'data' => $profits2,
                    'backgroundColor' => '#98e3b0',
                    'borderColor' => '#555',
                    'borderWidth' => 1,
                ]
            ],
        ]);

        $productProfitChart->setOptions([
            'responsive' => true,
            'plugins' => [
                'legend' => ['display' => true],
            ]
        ]);

        return $this->render('home/index.html.twig', [
            'products' => $productRepository->count(),
            'workflows' => $workflowRepository->count(),
            'chart' => $productProfitChart,
        ]);
    }
}
