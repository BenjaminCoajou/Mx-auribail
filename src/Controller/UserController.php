<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user/{id}", name="user")
     */
    public function index(EntityManagerInterface $em, int $id)
    {
        $userRepo = $em->getRepository(User::class);

        $user = $userRepo->find($id);

        return $this->render('user/index.html.twig', compact('user'));
    }

    /**
     * @Route("/user/edit/{user}",name="user_edit")
     */
    public function editUser(Request $request, User $user, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            //Si un nouveau password a été renseigné
            if($form->get('plainPassword')->getData())
            {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
