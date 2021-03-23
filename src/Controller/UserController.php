<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Form\EditUserFormType;
use App\Form\PassUserFormType;
use App\Form\CreateUserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="users")
     * @return Response
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/users/create", name="create_user")
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(CreateUserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Nowy rekord zapisany poprawnie');

            return $this->redirectToRoute('users');
        }

        return $this->render('user/create.html.twig', [
            'user_form' => $form->createView()
        ]);
    }

    /**
     * @Route("/users/show/{id}", name="show_user")
     * @return Response
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'role' => $user->getRolesDescription($user->getRoles())[0]
        ]);
    }

    /**
     * @Route("/users/edit/{id}", name="edit_user")
     * @return Response
     */
    public function edit(Request $request, User $user, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $form = $this->createForm(EditUserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Edycja zakończona poprawnie');

            return $this->redirectToRoute('users');
        }

        return $this->render('user/edit.html.twig', [
            'user_form' => $form->createView()
        ]);
    }

    /**
     * @Route("/users/pass/{id}", name="pass_user")
     * @return Response
     */
    public function pass(Request $request, User $user, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $form = $this->createForm(PassUserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Hasło zmienione poprawnie');

            return $this->redirectToRoute('users');
        }

        return $this->render('user/pass.html.twig', [
            'user_form' => $form->createView(),
            'username' => $user->getUsername()
        ]);
    }

    /**
     * @Route("/users/delete/{id}", name="delete_user")
     * @return Response
     */
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        try {
            $em->remove($user);
            $em->flush();
        }
        catch ( Exception $e )
        {
            $this->addFlash('danger', 'Wystąpiły błędy podczas usuwania rekordu');
            return $this->redirectToRoute('users');
        }
        $this->addFlash('success', 'Rekord usunięto poprawnie');
        return $this->redirectToRoute('users');
    }
}
