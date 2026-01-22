<?php

namespace App\Controller;

use App\Entity\Step;
use App\Entity\Workflow;
use App\Form\StepType;
use App\Form\WorkflowType;
use App\Repository\ProductRepository;
use App\Repository\WorkflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/processus', 'app.dashboard.workflow.')]
final class WorkflowController extends AbstractController
{
    #[Route('/nouveau/{productId}', name: 'create')]
    public function create(ProductRepository $productRepository, int $productId, Request $request, WorkflowRepository $workflowRepository, EntityManagerInterface $entityManager): Response
    {
        $workflow = new Workflow();
        $product = $productRepository->find($productId);

        $workflow->setName("Processus " . $workflowRepository->count() + 1);

        $form = $this->createForm(WorkflowType::class, $workflow, [
            'submit_label' => '<i class="plus icon"></i>Ajouter',
            'submit_class' => 'ui principal button',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workflow->setProduct($product);
            $entityManager->persist($workflow);
            $entityManager->flush();

            $this->addFlash('success', 'Processus ajouté avec succès !');

            return $this->redirectToRoute('app.dashboard.product.update', ['id' => $product->getId()]);
        }

        return $this->render('workflow/create.html.twig', [
            'form' => $form->createView(),
            'productId' => $product->getId(),
        ]);
    }

    #[Route('/modifier/{id}', name: 'update')]
    public function update(Workflow $workflow, Request $request, ChartBuilderInterface $builder, WorkflowRepository $workflowRepository, EntityManagerInterface $entityManager): Response
    {
        $step = new Step();
        $form = $this->createForm(WorkflowType::class, $workflow, [
            'submit_label' => '<i class="save icon"></i>Mettre à jour',
            'submit_class' => 'ui principal button',
        ]);
        $form->handleRequest($request);

        $formCreateStep = $this->createForm(StepType::class, $step, [
            'submit_label' => '<i class="plus icon"></i>Ajouter',
            'submit_class' => 'ui principal button',
            'action' => $this->generateUrl('app.dashboard.steps.create', ['workflowId' => $workflow->getId()]),
        ]);
        $formCreateStep->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($workflow);
            $entityManager->flush();

            $this->addFlash('success', 'Processus modifié avec succès !');

            return $this->redirectToRoute('app.dashboard.workflow.update', ['id' => $workflow->getId()]);
        }


        // Statistiques

        $chart = $builder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => $workflow->getSteps()->map(function (Step $step) {return $step->getName();})->toArray(),
            'datasets' => [
                [
                    'label' => $workflow->getName(),
                    'backgroundColor' => 'rgba(169, 212, 157, 0.6)',
                    'fill' => true,
                    'data' => $workflow->getSteps()->map(function (Step $step) {return $step->getIncome() - $step->getCost();})->toArray(),
                    'spanGaps' => true,
                ]
            ]
        ]);



        return $this->render('workflow/update.html.twig', [
            'form' => $form->createView(),
            'formCreateStep' => $formCreateStep->createView(),
            'workflow' => $workflow,
            'chart' => $chart,
        ]);
    }

    #[Route('/supprimer/{id}', name: 'delete')]
    public function delete(Workflow $workflow, EntityManagerInterface $entityManager): Response
    {
        $productId = $workflow->getProduct()->getId();
        $entityManager->remove($workflow);
        $entityManager->flush();

        $this->addFlash('success', 'Processus supprimé avec succès !');

        return $this->redirectToRoute('app.dashboard.product.update', ['id' => $productId]);
    }
}
