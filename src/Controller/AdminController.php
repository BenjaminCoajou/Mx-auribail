<?php

namespace App\Controller;

use App\Entity\Training;
use App\Entity\User;
use App\Entity\UserTraining;
use App\Form\TrainingType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Mailer;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Snappy\Pdf;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

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

    /**
     * @Route("/admin/trainig/create", name="admin_training_create")
     */
    public function createTraining(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(TrainingType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $training = new Training();
            $training->setTrainingDate($form->get('trainingDate')->getData());
            $training->setSlot($form->get('slot')->getData());
            $training->setInfo($form->get('info')->getData());
            $training->setOpeningRegistrationDate($form->get('openingRegistrationDate')->getData());
            $training->setAdult($form->get('adult')->getData());

            $em->persist($training);
            $em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/training/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/training/edit/{training}",name="admin_training_edit")
     */
    public function editTraining(Request $request, Training $training, EntityManagerInterface $em)
    {
        $form = $this->createForm(TrainingType::class, $training);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($training);
            $em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/training/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/training/delete/{training}",name="admin_training_delete")
     */
    public function deleteTraining(Mailer $mailer,Request $request, Training $training, EntityManagerInterface $em)
    {
        $builder = $this->createFormBuilder();
        $builder->add('Valider', SubmitType::class);

        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {


            $listUserTrainings = $training->getUserTrainings();
            foreach($listUserTrainings as $userTraining)
            {
                $user = $userTraining->getUser();
                $subjet = "Test Mail Delete";
                $msg = "Test msg Delete / Annulation !";
                $mailer->sendEmail($user,$subjet,$msg);
            }


            $em->remove($training);
            $em->flush();


            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/training/delete.html.twig', ['form' => $form->createView()]);
    }


    /**
     * @Route("/admin/training/list",name="admin_training_list")
     */
    public function listTrainings(EntityManagerInterface $em)
    {
        $repoUserTraining = $em->getRepository(UserTraining::class);
        
        $list = $repoUserTraining->findAll();

        return $this->render('admin/training/list.html.twig', compact('list'));
    }

    /**
     * @Route("/admin/training/pdf/{training}",name="admin_training_pdf")
     */
    public function pdfTraining(Request $request, UserTraining $userTraining, Training $training, EntityManagerInterface $em, Pdf $snappy)
    {
        $repoUserTraining = $em->getRepository(UserTraining::class);
        $pdf = $repoUserTraining->findBy([
            'training' => $training
        ]);

        $train = $training;

        //return $this->render('admin/training/pdf.html.twig', compact('pdf', 'train'));
        
        $html = $this->renderView('admin/training/pdf.html.twig', array(
            'pdf' => $pdf,
            'train' => $train
        ));
        
        $filename = 'Entrainement-'.$train->getTrainingDate()->format('d-m-y');

        return new PdfResponse(
            $snappy->getOutputFromHtml($html),
            $filename.'.pdf"'
            
        );
    }

}
