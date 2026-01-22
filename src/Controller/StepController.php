<?php

namespace App\Controller;

use App\Entity\Step;
use App\Entity\Workflow;
use App\Form\StepType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/etapes', 'app.dashboard.steps.')]
final class StepController extends AbstractController
{
    #[Route('/create/{workflowId}', name: 'create')]
    public function create(Workflow $workflowId, EntityManagerInterface $entityManager, Request $request)
    {
        $step = new Step();

        $form = $this->createForm(StepType::class, $step, [
            'submit_label' => '<i class="plus icon"></i>Ajouter',
            'submit_class' => 'ui principal button'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $step->setWorkflow($workflowId);
            $step->setStepNumber($workflowId->getSteps()->count() + 1);

            $entityManager->persist($step);
            $entityManager->flush();

            $this->addFlash('success', 'Étape ajoutée avec succès !');

            return $this->redirectToRoute('app.dashboard.workflow.update', ['id' => $workflowId->getId()]);
        }
    }

    #[Route('/modifier/{id}', name: 'update')]
    public function update(Step $step, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StepType::class, $step, [
            'submit_label' => '<i class="save icon"></i>Mettre à jour',
            'submit_class' => 'ui principal button'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($step);
            $entityManager->flush();

            $this->addFlash('success', 'Étape modifiée avec succès !');

            return $this->redirectToRoute('app.dashboard.workflow.update', ['id' => $step->getWorkflow()->getId()]);
        }

        return $this->render('/step/update.html.twig', [
            'form' => $form->createView(),
            'step' => $step,
        ]);
    }

    #[Route('/supprimer/{id}', name: 'delete')]
    public function delete(Step $step, EntityManagerInterface $entityManager): Response
    {
        $workflowId = $step->getWorkflow()->getId();
        $entityManager->remove($step);
        $entityManager->flush();

        $this->addFlash('sucess', 'Étape supprimée avec succès !');

        return $this->redirectToRoute('app.dashboard.workflow.update', ['id' => $workflowId]);
    }

    #[Route('/reorder/{id}', name: 'reorder', methods: ['POST'])]
    public function reorder(Workflow $workflow, Request $request, EntityManagerInterface $em): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        if (!isset($data['order']) || !is_array($data['order'])) {
            return $this->json([
                'error' => 'Invalid payload'
            ], 400);
        }

        $orderedIds = $data['order'];

        $steps = $workflow->getSteps();

        $stepsById = [];

        foreach ($steps as $step) {
            $stepsById[$step->getId()] = $step;
        }

        foreach ($orderedIds as $index => $stepId) {

            if (!isset($stepsById[$stepId])) {
                continue;
            }

            $stepsById[$stepId]->setStepNumber($index);
        }

        $em->flush();

        return $this->json([
            'success' => true
        ]);

    }
}
