<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\User;
use App\Form\LessonType;
use App\Repository\LessonRepository;
use cebe\markdown\Markdown as Markdown;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/lesson')]
class LessonController extends AbstractController
{
    private $security;
    private EntityManagerInterface $entityManager;
    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }
    #[Route('/', name: 'app_lesson_index', methods: ['GET'])]
    public function index(LessonRepository $lessonRepository): Response
    {

        if (!$this->getUser()) {
            return $this->render('security/notFound.html.twig');
        }
        /** @var User $user */
        $user = $this->security->getUser();
        $role = $user->getRole();

        if ($role == "professor" || $role=="admin") {
            $userId = $user->getId();
            $lessons = $this->entityManager->getRepository(Lesson::class)->findBy(['user' => $userId]);
        } else {
            $lessons = $lessonRepository->findAll();
        }
        return $this->render('lesson/index.html.twig', [
            'lessons' => $lessons,
        ]);
    }


    #[Route('/prof/{id}/edit/displaySubscribedStudents', name: 'app_lesson_display_students', methods: ['GET'])]
    public function displaySubscribedStudents(Request $request, Lesson $lesson, int $id, EntityManagerInterface $entityManager): Response
    {

        if (!$this->getUser()) {
            return $this->render('security/notFound.html.twig');
        }
        /** @var User $user */
        $user = $this->security->getUser();
        $role = $user->getRole();

        if ($role == "professor" || $role=="admin") {
            $students = $lesson->getUsers();
        } else {
            return $this->render('security/noPermession.html.twig');
        }
        return $this->render('lesson/displayStudents.html.twig', [
            'students' => $students,
            'lesson' => $lesson,
        ]);
    }

    #[Route('/prof/new', name: 'app_lesson_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Markdown $markdown): Response
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            /** @var User $user */
            $user = $this->security->getUser();
            $role = $user->getRole();
            if ($role == "professor" || $role=="admin") {
                $lesson = new Lesson();
                $form = $this->createForm(LessonType::class, $lesson);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager->persist($lesson);
                    // writing the description in Markdown format 
                    $lesson->setDescription($markdown->parse($lesson->getDescription()));
                    $lesson->setUser($user);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_lesson_index', [], Response::HTTP_SEE_OTHER);
                }

                return $this->render('lesson/new.html.twig', [
                    'lesson' => $lesson,
                    'form' => $form,
                ]);
            } else {
                return $this->render('security/noPermession.html.twig');
            }
        } else {
            return $this->render('security/notFound.html.twig');
        }
    }

    #[Route('/{id}', name: 'app_lesson_show', methods: ['GET'])]
    public function show(Lesson $lesson): Response
    {
        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
        ]);
    }

    #[Route('/prof/{id}/edit', name: 'app_lesson_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lesson $lesson, int $id, EntityManagerInterface $entityManager): Response
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            /** @var User $user */ 
            $user = $this->security->getUser();
            $role = $user->getRole();
            $lessonById = $entityManager->getRepository(Lesson::class)->find($id);
            $userFromLesson = $lessonById->getUser();
            if (($role == "professor" || $role == "admin") && $user == $userFromLesson) {
                $form = $this->createForm(LessonType::class, $lesson);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager->flush();

                    return $this->redirectToRoute('app_lesson_index', [], Response::HTTP_SEE_OTHER);
                }

                return $this->render('lesson/edit.html.twig', [
                    'lesson' => $lesson,
                    'form' => $form,
                ]);
            } else {
                return $this->render('security/noPermession.html.twig');
            }
        } else {
            return $this->render('security/notFound.html.twig');
        }
    }

    #[Route('/prof/{id}', name: 'app_lesson_delete', methods: ['POST'])]
    public function delete(Request $request, Lesson $lesson, int $id, EntityManagerInterface $entityManager): Response
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            /** @var User $user */
            $user = $this->security->getUser();
            $role = $user->getRole();
            $lessonById = $entityManager->getRepository(Lesson::class)->find($id);
            $userFromLesson = $lessonById->getUser();
            if (($role == "professor" || $role == "admin") && $user == $userFromLesson) {
                if ($this->isCsrfTokenValid('delete' . $lesson->getId(), $request->request->get('_token'))) {
                    $entityManager->remove($lesson);
                    $entityManager->flush();
                }
            } else {
                return $this->render('security/noPermession.html.twig');
            }
        } else {
            return $this->render('security/notFound.html.twig');
        }

        return $this->redirectToRoute('app_lesson_index', [], Response::HTTP_SEE_OTHER);
    }

    //Student with lessons 
    #[Route('/etudiant/{id}/subscribe', name: 'app_lesson_subscribe', methods: ['GET'])]
    public function subscribeLesson(Request $request, Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            /** @var User $user */
            $user = $this->security->getUser();
            $role = $user->getRole();
            if ($role == "Etudiant") {
                $lesson->addUser($user);
                $entityManager->flush();
                return $this->redirectToRoute('app_lesson_index');
            } else {
                return $this->render('security/noPermession.html.twig');
            }
        } else {
            return $this->render('security/notFound.html.twig');
        }
    }

    #[Route('/etudiant/{id}/unsubscribe', name: 'app_lesson_unsubscribe', methods: ['GET'])]
    public function unsubscribeLesson(Request $request, Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            /** @var User $user */
            $user = $this->security->getUser();
            $role = $user->getRole();
            if ($role == "Etudiant") {
                $lesson->removeUser($user);
                $entityManager->flush();
                return $this->redirectToRoute('app_lesson_index');
            } else {
                return $this->render('security/noPermession.html.twig');
            }
        } else {
            return $this->render('security/notFound.html.twig');
        }
    }

    #[Route('/etudiant/displayMyLessons', name: 'app_myLesson_display', methods: ['GET'])]
    public function displayMyLessons(): Response
    {
        if (!$this->getUser()) {
            return $this->render('security/notFound.html.twig');
        }
        /** @var User $user */
        $user = $this->security->getUser();
        $role = $user->getRole();
        if ($role == "Etudiant") {
            $lessons = $user->getLessons();
        } else {
            return $this->render('security/noPermession.html.twig');
        }
        return $this->render('lesson/MyLessons.html.twig', [
            'lessons' => $lessons,
        ]);
    }
}
