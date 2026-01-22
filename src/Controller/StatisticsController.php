<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\WorkflowRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/statistiques', 'app.dashboard.statistics.')]
final class StatisticsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductRepository $productRepository, WorkflowRepository $workflowRepository, ChartBuilderInterface $builder): Response
    {
        $chart = $builder->createChart(Chart::TYPE_BAR);

        $datasets = [

        ];
        foreach ($productRepository->findAll() as $productName) {
            $workflowsName[] = $productName->getName();
        }

        $chart->setData([
            'labels' => $workflowsName,
            'datasets' => [
                [
                    'label' => 'Rentabilité / Processus',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $this->render('statistics/index.html.twig', [
            'chart' => $chart,
        ]);
    }
}
