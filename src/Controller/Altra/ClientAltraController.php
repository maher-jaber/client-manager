<?php

namespace App\Controller\Altra;

use App\Entity\Client;
use App\Entity\ClientActionLog;
use App\Entity\Society;
use App\Form\ClientType;
use App\Repository\ActionRepository;
use App\Repository\ClientRepository;
use App\Service\MailerService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


#[Route('/dashboard/altra/client')]
final class ClientAltraController extends AbstractController
{
    #[Route('/', name: 'app_client_index_altra')]
    public function index(EntityManagerInterface $em, PaginatorInterface $paginator, ActionRepository $actionRepo, Request $request): Response
    {


        if (!$this->getUser()->hasPermission('ALTRA => Client : List')) {
            throw $this->createAccessDeniedException();
        }
        $societyRepo = $em->getRepository(Society::class);
        $altra = $societyRepo->findOneBy(['label' => 'ALTRA']);
        
        $queryBuilder = $em->getRepository(Client::class)
            ->createQueryBuilder('c')
            ->andWhere('c.entite = :ids')
            ->setParameter('ids', $altra->getId())
            ->orderBy('c.nomClient', 'ASC');

        // Filtre par commercial
        if ($commercial = $request->query->get('commercial')) {
            $queryBuilder->andWhere('c.commercial = :commercial')
                ->setParameter('commercial', $commercial);
        }

        // Recherche globale
        if ($search = $request->query->get('q')) {
            $queryBuilder->andWhere('c.nomClient LIKE :search OR c.email LIKE :search OR c.numeroContrat LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if ($dateFrom = $request->query->get('date_from')) {
            $from = \DateTime::createFromFormat('Y-m-d', $dateFrom);
            if ($from) {
                $from->setTime(00, 00, 00);
                $queryBuilder->andWhere('c.dernierEnvoiMail >= :fromDate')
                    ->setParameter('fromDate', $from)
                    ->orderBy('c.dernierEnvoiMail', 'ASC');
            }
        }

        if ($dateTo = $request->query->get('date_to')) {
            $to = \DateTime::createFromFormat('Y-m-d', $dateTo);
            if ($to) {
                // Ajouter 1 jour pour inclure toute la journée sélectionnée
                $to->setTime(23, 59, 59);
                $queryBuilder->andWhere('c.dernierEnvoiMail <= :toDate')
                    ->setParameter('toDate', $to)
                    ->orderBy('c.dernierEnvoiMail', 'ASC');
            }
        }
        // Récupération des commerciaux distincts
        $commerciaux = $em->getRepository(Client::class)
            ->createQueryBuilder('c')
            ->select('DISTINCT c.commercial')
            ->where('c.commercial IS NOT NULL')
            ->orderBy('c.commercial', 'ASC')
            ->getQuery()
            ->getResult();

        $commerciaux = array_column($commerciaux, 'commercial');

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10
        );
        $allActions = $actionRepo->findBy(['entite'=>$altra]);
        return $this->render('ALTRA/client/index.html.twig', [
            'pagination' => $pagination,
            'commerciaux' => $commerciaux,
            'all_actions' => $allActions,
        ]);
    }



    #[Route('/bulk-actions', name: 'app_client_bulk_actions_altra', methods: ['POST'])]
    public function bulkActions(
        Request $request,
        ClientRepository $clientRepo,
        ActionRepository $actionRepo,
        EntityManagerInterface $em,
        MailerService $mailerService
    ): Response {


        
        if (!$this->getUser()->hasPermission('ALTRA => Client : Bulk_action')) {
            throw $this->createAccessDeniedException();
        }

        $clientIds = $request->request->all('clients');
        $actionIds = $request->request->all('actions');

        // ✅ Vérification : aucun client sélectionné
        if (empty($clientIds)) {
            $this->addFlash('warning', 'Veuillez sélectionner au moins un client.');
            return $this->redirectToRoute('app_client_index_altra');
        }

        // ✅ Vérification : aucune action sélectionnée
        if (empty($actionIds)) {
            $this->addFlash('warning', 'Veuillez sélectionner au moins une action à appliquer.');
            return $this->redirectToRoute('app_client_index_altra');
        }

        $actions = $actionRepo->findBy(['id' => $actionIds]);

        // Vérification si tous les clients ont un email
        $clients = $clientRepo->findBy(['id' => $clientIds]);
        $clientsWithoutEmail = array_filter($clients, fn(Client $c) => empty($c->getEmail()));

        if (!empty($clientsWithoutEmail)) {
            $noms = array_map(fn(Client $c) => $c->getNomClient(), $clientsWithoutEmail);
            $this->addFlash('danger', 'Aucun email envoyé : les clients suivants n\'ont pas d\'adresse email : ' . implode(', ', $noms));
            return $this->redirectToRoute('app_client_index_altra');
        }
        $societyRepo = $em->getRepository(Society::class);
        $altra = $societyRepo->findOneBy(['label' => 'ALTRA']);

        // Tous les clients ont un email → on peut continuer
        foreach ($clients as $client) {
            $originalActions = new ArrayCollection($client->getActions()->toArray());

            $added = [];
            $removed = [];

            foreach ($actions as $a) {

                $added[] = $a;
            }



            if (!empty($added)) {
                $htmlBody = $this->renderView('ALTRA/emails/actions_update.html.twig', [
                    'client' => $client,
                    'added' => $added,
                    'removed' => $removed,
                ]);
                $mailerService->sendMail(
                    $client->getEmail(),
                    $client->getNomClient(),
                    'Mise à jour de vos actions',
                    $htmlBody,
                    'ALTRA'
                );
                $client->setDernierEnvoiMail(new \DateTimeImmutable());
            }
            $log = new ClientActionLog();
            $log->setPerformedAt(new \DateTime());
            $log->setPerformedBy($this->getUser()?->getUserIdentifier() ?? 'system');

            $log->setEntite($altra);
            $log->addClient($client);


            foreach ($actions as $action) {
                $log->addAction($action);
            }

            $em->persist($log);
        }


        $em->flush();

        $this->addFlash('success', 'Actions mises à jour pour les clients sélectionnés.');
        return $this->redirectToRoute('app_client_index_altra');
    }


    #[Route('/new', name: 'app_client_new_altra', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()->hasPermission('ALTRA => Client : Create')) {
            throw $this->createAccessDeniedException();
        }
        $societyRepo = $entityManager->getRepository(Society::class);
        $altra = $societyRepo->findOneBy(['label' => 'ALTRA']);

        $client = new Client();
        $form = $this->createForm(ClientType::class, $client, [
            'entreprise' => $altra,
        ]);
        $form->handleRequest($request);

        // Récupère l'URL précédente (page liste par défaut)
        $referer = $request->headers->get('referer') ?? $this->generateUrl('app_client_index_altra');

       
    

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setEntite($altra);
            $entityManager->persist($client);

            $entityManager->flush();

            return $this->redirectToRoute('app_client_index_altra');
        }

        return $this->render('ALTRA/client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_edit_altra', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Client $client,
        EntityManagerInterface $entityManager,
        MailerService $mailerService
    ): Response {

        $user = $this->getUser();
        // Vérifie que l'utilisateur a la permission 'edit' sur l'entité 'client'
        if (!$user->hasPermission('ALTRA => Client : Edit')) {
            throw $this->createAccessDeniedException('Accès refusé : vous n\'avez pas la permission d\'éditer un client.');
        }
        $societyRepo = $entityManager->getRepository(Society::class);
        $altra = $societyRepo->findOneBy(['label' => 'ALTRA']);

        $form = $this->createForm(ClientType::class, $client, [
            'entreprise' => $altra,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Action[] $selectedActions */
            $selectedActions = $form->get('actions')->getData(); // actions cochées (mais pas enregistrées)

            // Envoyer l’email s’il y a des actions sélectionnées
            if (count($selectedActions) > 0) {
                $htmlBody = $this->renderView('ALTRA/emails/actions_update.html.twig', [
                    'client' => $client,
                    'added' => array_map(fn($a) => $a, iterator_to_array($selectedActions)),
                    'removed' => [], // aucune suppression dans ce modèle
                ]);
                $mailerService->sendMail(
                    $client->getEmail(),
                    $client->getNomClient(),
                    'Mise à jour des actions vous concernant',
                    $htmlBody,
                    'ALTRA'
                );
                $client->setDernierEnvoiMail(new \DateTimeImmutable());
            }

            // Journaliser les actions appliquées
            $currentUser = $this->getUser()?->getUserIdentifier() ?? 'system';
            $this->logClientAction(
                [$client],
                $selectedActions, // les actions sélectionnées
                $currentUser,
                'Modification des actions du client',
                null, // pas d’original ici
                $entityManager
            );

            $this->addFlash('success', 'Client mis à jour. Actions logguées.');

            $entityManager->flush();

            return $this->redirectToRoute('app_client_index_altra');
        }

        return $this->render('ALTRA/client/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/{id}', name: 'app_client_delete_altra', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()->hasPermission('ALTRA => Client : Delete')) {
            throw $this->createAccessDeniedException();
        }
        $referer = $request->headers->get('referer') ?? $this->generateUrl('app_client_index_altra');

        if ($this->isCsrfTokenValid('delete' . $client->getId(), $request->request->get('_token'))) {
            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirect($referer);
    }

    #[Route('/{id}', name: 'app_client_show_altra', methods: ['GET'])]
    public function show(Request $request, Client $client): Response
    {
        if (!$this->getUser()->hasPermission('ALTRA => Client : View')) {
            throw $this->createAccessDeniedException();
        }
        $referer = $request->headers->get('referer') ?? $this->generateUrl('app_client_index_altra');

        return $this->render('ALTRA/client/show.html.twig', [
            'client' => $client,
            'referer' => $referer,
        ]);
    }

    private function logClientAction(
        array $clients,
        iterable $newActions,
        string $performedBy,
        string $note,
        ?Collection $oldActions = null,
        EntityManagerInterface $em,
    ): void {
        $societyRepo = $em->getRepository(Society::class);
        $altra = $societyRepo->findOneBy(['label' => 'ALTRA']);

        $log = new ClientActionLog();
        $log->setPerformedAt(new \DateTime());
        $log->setPerformedBy($performedBy);
        $log->setNote($note);
        $log->setEntite($altra);

        foreach ($clients as $client) {
            $log->addClient($client);
        }

        foreach ($newActions as $action) {
            $log->addAction($action);
        }

        if ($oldActions !== null) {
            $removed = array_filter(
                $oldActions->toArray(),
                fn($a) => !in_array($a, $newActions instanceof Collection ? $newActions->toArray() : $newActions, true)
            );
            if (count($removed) > 0) {
                $note .= ' | Actions supprimées : ';
                foreach ($removed as $a) {
                    $note .= $a->getLabel() . ', ';
                }
                $log->setNote($note);
            }
        }

        $em->persist($log);
        $em->flush();
    }
}
