<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/professors')]
class ProfController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_prof_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $profs = $this->entityManager->getRepository(User::class)->findBy(['role' => ['professor', 'admin']]);
        return $this->render('prof/index.html.twig', [
            'users' => $profs,
        ]);
    }

    #[Route('/new', name: 'app_prof_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher,): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRole('professor');
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_prof_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prof/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_prof_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('prof/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_prof_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, int $id, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->find($id);
        $role = $user->getRole();

        if ($role == "professor") {
            $user->setRole("admin");
        } else {
            $user->setRole("professor");
        }
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_prof_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_prof_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_prof_index', [], Response::HTTP_SEE_OTHER);
    }
}
