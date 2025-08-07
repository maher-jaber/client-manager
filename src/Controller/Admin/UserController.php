<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/user')]
class UserController extends AbstractController
{
	#[Route('/', name: 'admin_user_index')]
	public function index(UserRepository $userRepository): Response
	{
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
		return $this->render('admin/user/index.html.twig', [
			'users' => $userRepository->findAll(),
		]);
	}

	#[Route('/new', name: 'admin_user_new')]
	public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
	{
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
		$user = new User();
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$password = $form->get('password')->getData();
			if ($password) {
				$user->setPassword($hasher->hashPassword($user, $password));
			}

			$em->persist($user);
			$em->flush();

			return $this->redirectToRoute('admin_user_index');
		}

		return $this->render('admin/user/form.html.twig', [
			'form' => $form->createView(),
			'user' => $user,
		]);
	}

	#[Route('/{id}/edit', name: 'admin_user_edit')]
	public function edit(User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
	{
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$password = $form->get('password')->getData();
			if ($password) {
				$user->setPassword($hasher->hashPassword($user, $password));
			}
			$em->flush();

			return $this->redirectToRoute('admin_user_index');
		}

		return $this->render('admin/user/form.html.twig', [
			'form' => $form->createView(),
			'user' => $user,
		]);
	}

	#[Route('/{id}', name: 'admin_user_delete', methods: ['POST'])]
	public function delete(User $user, Request $request, EntityManagerInterface $em): Response
	{
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
		if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
			$em->remove($user);
			$em->flush();
		}

		return $this->redirectToRoute('admin_user_index');
	}
}
