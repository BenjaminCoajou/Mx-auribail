<?php

namespace App\Controller;

use App\Entity\Training;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(EntityManagerInterface $em): Response
    {
        $repoUser = $em->getRepository(User::class);
        $repoTraining = $em->getRepository(Training::class);
        $users = $repoUser->findAll();
        $trainings = $repoTraining->findAll();
        return $this->render('admin/index.html.twig', compact('users', 'trainings'));
    }

    /**
     * @Route("/admin/user/edit/{user}", name="admin_user_edit")
     */
    public function editUser(Request $request, User $user, EntityManagerInterface $em)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/admin/user/delete/{user}", name="admin_user_delete")
     */
    public function deleteUser(Request $request, User $user, EntityManagerInterface $em)
    {
        $builder = $this->createFormBuilder();
        $builder->add('Valider', SubmitType::class);

        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            
            $em->remove($user);
            $em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/user/delete.html.twig', ['form' => $form->createView()]);
    }
}
