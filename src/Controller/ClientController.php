<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\ClientActionLog;
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


#[Route('/client')]
final class ClientController extends AbstractController
{
    #[Route('/', name: 'app_client_index')]
    public function index(EntityManagerInterface $em, PaginatorInterface $paginator, ActionRepository $actionRepo, Request $request): Response
    {
        $queryBuilder = $em->getRepository(Client::class)
            ->createQueryBuilder('c')
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
        $allActions = $actionRepo->findAll();
        return $this->render('client/index.html.twig', [
            'pagination' => $pagination,
            'commerciaux' => $commerciaux,
            'all_actions' => $allActions,
        ]);
    }



    #[Route('/bulk-actions', name: 'app_client_bulk_actions', methods: ['POST'])]
    public function bulkActions(
        Request $request,
        ClientRepository $clientRepo,
        ActionRepository $actionRepo,
        EntityManagerInterface $em,
        MailerService $mailerService
    ): Response {
        $clientIds = $request->request->all('clients');
        $actionIds = $request->request->all('actions');

        // ✅ Vérification : aucun client sélectionné
        if (empty($clientIds)) {
            $this->addFlash('warning', 'Veuillez sélectionner au moins un client.');
            return $this->redirectToRoute('app_client_index');
        }

        // ✅ Vérification : aucune action sélectionnée
        if (empty($actionIds)) {
            $this->addFlash('warning', 'Veuillez sélectionner au moins une action à appliquer.');
            return $this->redirectToRoute('app_client_index');
        }

        $actions = $actionRepo->findBy(['id' => $actionIds]);

        // Vérification si tous les clients ont un email
        $clients = $clientRepo->findBy(['id' => $clientIds]);
        $clientsWithoutEmail = array_filter($clients, fn(Client $c) => empty($c->getEmail()));

        if (!empty($clientsWithoutEmail)) {
            $noms = array_map(fn(Client $c) => $c->getNomClient(), $clientsWithoutEmail);
            $this->addFlash('danger', 'Aucun email envoyé : les clients suivants n\'ont pas d\'adresse email : ' . implode(', ', $noms));
            return $this->redirectToRoute('app_client_index');
        }

        // Tous les clients ont un email → on peut continuer
        foreach ($clients as $client) {
            $originalActions = new ArrayCollection($client->getActions()->toArray());

            $added = [];
            $removed = [];

            foreach ($actions as $a) {
                
                    $added[] = $a->getLabel();
                
            }

           

            if (!empty($added)) {
                $htmlBody = $this->renderView('emails/actions_update.html.twig', [
                    'client' => $client,
                    'added' => $added,
                    'removed' => $removed,
                ]);
                $mailerService->sendMail(
                    $client->getEmail(),
                    $client->getNomClient(),
                    'Mise à jour de vos actions',
                    $htmlBody
                );
            }
            $log = new ClientActionLog();
            $log->setPerformedAt(new \DateTime());
            $log->setPerformedBy($this->getUser()?->getUserIdentifier() ?? 'system');


            $log->addClient($client);


            foreach ($actions as $action) {
            $log->addAction($action);
            }

            $em->persist($log);
        }


        $em->flush();

        $this->addFlash('success', 'Actions mises à jour pour les clients sélectionnés.');
        return $this->redirectToRoute('app_client_index');
    }


    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        // Récupère l'URL précédente (page liste par défaut)
        $referer = $request->headers->get('referer') ?? $this->generateUrl('app_client_index');

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirect($referer);
        }

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Client $client,
        EntityManagerInterface $entityManager,
        MailerService $mailerService
    ): Response {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Action[] $selectedActions */
            $selectedActions = $form->get('actions')->getData(); // actions cochées (mais pas enregistrées)

            // Envoyer l’email s’il y a des actions sélectionnées
            if (count($selectedActions) > 0) {
                $htmlBody = $this->renderView('emails/actions_update.html.twig', [
                    'client' => $client,
                    'added' => array_map(fn($a) => $a->getLabel(), iterator_to_array($selectedActions)),
                    'removed' => [], // aucune suppression dans ce modèle
                ]);
                $mailerService->sendMail(
                    $client->getEmail(),
                    $client->getNomClient(),
                    'Mise à jour des actions vous concernant',
                    $htmlBody
                );
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

            return $this->redirectToRoute('app_client_index');
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/{id}', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $referer = $request->headers->get('referer') ?? $this->generateUrl('app_client_index');

        if ($this->isCsrfTokenValid('delete' . $client->getId(), $request->request->get('_token'))) {
            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirect($referer);
    }

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    public function show(Request $request, Client $client): Response
    {
        $referer = $request->headers->get('referer') ?? $this->generateUrl('app_client_index');

        return $this->render('client/show.html.twig', [
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
        $log = new ClientActionLog();
        $log->setPerformedAt(new \DateTime());
        $log->setPerformedBy($performedBy);
        $log->setNote($note);

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
