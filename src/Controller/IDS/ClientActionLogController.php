<?php

namespace App\Controller\IDS;

use App\Entity\ClientActionLog;
use App\Form\ClientActionLogType;
use App\Repository\ClientActionLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/ids/client/action/log')]
final class ClientActionLogController extends AbstractController
{
    #[Route(name: 'app_client_action_log_index', methods: ['GET'])]
    public function index(Request $request,ClientActionLogRepository $clientActionLogRepository,PaginatorInterface $paginator): Response
    {
        if (!$this->getUser()->hasPermission('IDS => Log : List')) {
            throw $this->createAccessDeniedException();
        }
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
}
