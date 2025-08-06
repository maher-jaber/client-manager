<?php

namespace App\Controller;

use App\Entity\ClientActionLog;
use App\Form\ClientActionLogType;
use App\Repository\ClientActionLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client/action/log')]
final class ClientActionLogController extends AbstractController
{
    #[Route(name: 'app_client_action_log_index', methods: ['GET'])]
    public function index(Request $request,ClientActionLogRepository $clientActionLogRepository,PaginatorInterface $paginator): Response
    {

        $qb = $clientActionLogRepository->createQueryBuilder('l');
        $qb->orderBy('l.performedAt', 'DESC'); // Tri par défaut
        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            5
        );
        
        return $this->render('client_action_log/index.html.twig', [
            'client_action_logs' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_client_action_log_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $clientActionLog = new ClientActionLog();
        $form = $this->createForm(ClientActionLogType::class, $clientActionLog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($clientActionLog);
            $entityManager->flush();

            return $this->redirectToRoute('app_client_action_log_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client_action_log/new.html.twig', [
            'client_action_log' => $clientActionLog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_action_log_show', methods: ['GET'])]
    public function show(ClientActionLog $clientActionLog): Response
    {
        return $this->render('client_action_log/show.html.twig', [
            'client_action_log' => $clientActionLog,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_action_log_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ClientActionLog $clientActionLog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientActionLogType::class, $clientActionLog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_client_action_log_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client_action_log/edit.html.twig', [
            'client_action_log' => $clientActionLog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_action_log_delete', methods: ['POST'])]
    public function delete(Request $request, ClientActionLog $clientActionLog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$clientActionLog->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($clientActionLog);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_client_action_log_index', [], Response::HTTP_SEE_OTHER);
    }
}
