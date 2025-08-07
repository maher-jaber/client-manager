<?php

namespace App\Controller\IDS;

use App\Entity\Action;
use App\Form\ActionType;
use App\Repository\ActionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/dashboard/ids/action')]
final class ActionController extends AbstractController
{
    #[Route('/', name: 'app_action_index', methods: ['GET'])]
    public function index(Request $request, ActionRepository $actionRepository, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('search');
        $qb = $actionRepository->createQueryBuilder('a');

        if ($search) {
            $qb->where('a.label LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('a.label', 'ASC');

        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('action/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_action_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $action = new Action();
        $form = $this->createForm(ActionType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logoFile = $form->get('logo')->getData();

            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                try {
                    $logoFile->move(
                        $this->getParameter('action_logos_directory'),
                        $newFilename
                    );
                    $action->setLogo($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload du fichier : ' . $e->getMessage());
                }
            }

            $em->persist($action);
            $em->flush();

            $this->addFlash('success', 'Action créée avec succès.');
            return $this->redirectToRoute('app_action_index');
        }

        return $this->render('action/new.html.twig', [
            'action' => $action,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_action_show', methods: ['GET'])]
    public function show(Action $action): Response
    {
        return $this->render('action/show.html.twig', [
            'action' => $action,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_action_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Action $action,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(ActionType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logoFile = $form->get('logo')->getData();

            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                try {
                    $logoFile->move(
                        $this->getParameter('action_logos_directory'),
                        $newFilename
                    );

                    // Supprimer l'ancien logo s'il existe
                    $oldLogo = $action->getLogo();
                    if ($oldLogo && file_exists($this->getParameter('action_logos_directory') . '/' . $oldLogo)) {
                        @unlink($this->getParameter('action_logos_directory') . '/' . $oldLogo);
                    }

                    $action->setLogo($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload du fichier : ' . $e->getMessage());
                }
            }

            $em->flush();

            $this->addFlash('success', 'Action mise à jour avec succès.');
            return $this->redirectToRoute('app_action_index');
        }

        return $this->render('action/edit.html.twig', [
            'action' => $action,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_action_delete', methods: ['POST'])]
    public function delete(Request $request, Action $action, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $action->getId(), $request->getPayload()->getString('_token'))) {
            // Supprimer le logo si existant
            $logo = $action->getLogo();
            if ($logo && file_exists($this->getParameter('action_logos_directory') . '/' . $logo)) {
                @unlink($this->getParameter('action_logos_directory') . '/' . $logo);
            }

            $em->remove($action);
            $em->flush();

            $this->addFlash('success', 'Action supprimée.');
        }

        return $this->redirectToRoute('app_action_index');
    }
}
