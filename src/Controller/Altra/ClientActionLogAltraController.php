<?php

namespace App\Controller\Altra;

use App\Entity\ClientActionLog;
use App\Entity\Society;
use App\Form\ClientActionLogType;
use App\Repository\ClientActionLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/altra/client/action/log')]
final class ClientActionLogAltraController extends AbstractController
{
    #[Route(name: 'app_client_action_log_index_altra', methods: ['GET'])]
    public function index(EntityManagerInterface $em,Request $request,ClientActionLogRepository $clientActionLogRepository,PaginatorInterface $paginator): Response
    {
        if (!$this->getUser()->hasPermission('ALTRA => Log : List')) {
            throw $this->createAccessDeniedException();
        }
        $societyRepo = $em->getRepository(Society::class);
        $altra = $societyRepo->findOneBy(['label' => 'ALTRA']);

        $qb = $clientActionLogRepository->createQueryBuilder('l')
        ->andWhere('l.entite = :altra')
        ->setParameter('altra', $altra->getId());

        $qb->orderBy('l.performedAt', 'DESC'); // Tri par défaut
        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            5
        );
        
        return $this->render('ALTRA/client_action_log/index.html.twig', [
            'client_action_logs' => $pagination,
        ]);
    }
}
