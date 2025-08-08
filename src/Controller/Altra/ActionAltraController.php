<?php

namespace App\Controller\Altra;

use App\Entity\Action;
use App\Entity\Society;
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

#[Route('/dashboard/altra/action')]
final class ActionAltraController extends AbstractController
{
    #[Route('/', name: 'app_action_index_altra', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Request $request, ActionRepository $actionRepository, PaginatorInterface $paginator): Response
    {
        if (!$this->getUser()->hasPermission('ALTRA => Action : List')) {
            throw $this->createAccessDeniedException();
        }
        $societyRepo = $em->getRepository(Society::class);
        $altra = $societyRepo->findOneBy(['label' => 'ALTRA']);

        $search = $request->query->get('search');
        $qb = $actionRepository->createQueryBuilder('a')
            ->andWhere('a.entite = :altra')
            ->setParameter('altra', $altra->getId());

        if ($search) {
            $qb->where('a.label LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('a.label', 'ASC');

        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('ALTRA/action/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_action_new_altra', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if (!$this->getUser()->hasPermission('ALTRA => Action : Create')) {
            throw $this->createAccessDeniedException();
        }
        $action = new Action();
        $form = $this->createForm(ActionType::class, $action);
        $form->handleRequest($request);


        $societyRepo = $em->getRepository(Society::class);
        $altra = $societyRepo->findOneBy(['label' => 'ALTRA']);



        if ($form->isSubmitted() && $form->isValid()) {

            $action->setEntite($altra);

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
            return $this->redirectToRoute('app_action_index_altra');
        }

        return $this->render('ALTRA/action/new.html.twig', [
            'action' => $action,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_action_show_altra', methods: ['GET'])]
    public function show(Action $action): Response
    {
        if (!$this->getUser()->hasPermission('ALTRA => Action : View')) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('ALTRA/action/show.html.twig', [
            'action' => $action,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_action_edit_altra', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Action $action,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        if (!$this->getUser()->hasPermission('ALTRA => Action : Delete')) {
            throw $this->createAccessDeniedException();
        }

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
            return $this->redirectToRoute('app_action_index_altra');
        }

        return $this->render('ALTRA/action/edit.html.twig', [
            'action' => $action,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_action_delete_altra', methods: ['POST'])]
    public function delete(Request $request, Action $action, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()->hasPermission('ALTRA => Action : Delete')) {
            throw $this->createAccessDeniedException();
        }
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

        return $this->redirectToRoute('app_action_index_altra');
    }
}
