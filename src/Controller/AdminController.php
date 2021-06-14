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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        $members = $repoUser->findByRole('ROLE_MEMBER');
        return $this->render('admin/index.html.twig', compact('users', 'trainings', 'members'));
    }

    /**
     * @Route("/admin/users", name="list_users")
     */
    public function listUsers(EntityManagerInterface $em): Response
    {
        $repoUser = $em->getRepository(User::class);
        $users = $repoUser->findAll();
        return $this->render('admin/user/list.html.twig', compact('users'));
    }

    /**
     * @Route("/admin/user/edit/{user}", name="admin_user_edit")
     */
    public function editUser(Request $request,UserPasswordEncoderInterface $passwordEncoder, User $user, EntityManagerInterface $em)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $roles = $user->getRoles();
        $roleMember = "ROLE_MEMBER";
        $isMember = in_array($roleMember, $roles);

        if ($form->isSubmitted() && $form->isValid()) {
            //Si la checkbox "membre" à été cochée
            if($form->get('isMember')->getData())
            {
                //si l'user n'a pas deja le role ROLE_MEMBER, on le rajoute
                if (!$isMember) {
                    $roles[] = $roleMember;
                }
            }
            //si la checkbox n'a pas été cochée 
            else{
                //si l'user a le role ROLE_MEMBER, on l'enleve
                if ($isMember) {
                    unset($roles[array_search($roleMember, $roles)]);
                }
            }
            $user->setRoles($roles);

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

            return $this->redirectToRoute('admin');
        }
        
        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView(),
            'isMember' => $isMember,
            'user' => $user
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
     * @Route("/admin/training/create", name="admin_training_create")
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
    public function deleteTraining(Mailer $mailer, Request $request, Training $training, EntityManagerInterface $em)
    {
        $builder = $this->createFormBuilder();
        $builder->add('Valider', SubmitType::class);

        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $listUserTrainings = $training->getUserTrainings();
            foreach ($listUserTrainings as $userTraining) {
                $user = $userTraining->getUser();
                $subjet = "[Mx Park] - ANNULATION - Entrainement du ".$training->getTrainingDate()->format('d-m-Y');
                $msg = "Bonjour ".$user->getFirstname().",\n".
                "L'entrainement du ".$training->getTrainingDate()->format('d-m-Y')." est annulé.\n".
                "Nous nous excusons pour la gêne occasionnée.\n\n".
                "Cordialement,\n".
                "MX Park - Auribail";
                $mailer->sendEmail($user, $subjet, $msg);
            }

            $em->remove($training);
            $em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/training/delete.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/admin/training/list/{training}",name="admin_training_users_list")
     */
    public function listUsersTraining(EntityManagerInterface $em, Training $training)
    {
        $repoUserTraining = $em->getRepository(UserTraining::class);

        $list = $repoUserTraining->findBy(
            [
                'training' => $training,
            ],
            [
                'dateRegistration' => 'ASC',
            ]
        );

        $listNoLicence = [];
        foreach ($list as $haveLicence) {
            if ($haveLicence->getUser()->getLicence() == false) {
                $listNoLicence[] = $haveLicence;
                
            }
        }

        return $this->render('admin/training/list.html.twig', compact('list', 'training', 'listNoLicence'));
    }

    /**
     * @Route("admin/training/list/noLicence/{training}", name="admin_training_users_list_noLicence")
     */
    public function listUserWithoutLicence(Mailer $mailer, EntityManagerInterface $em, Training $training)
    {
        $repoUserTraining = $em->getRepository(UserTraining::class);

        $list = $repoUserTraining->findBy(
            [
                'training' => $training,
            ],
        );

        foreach ($list as $element) {
            $user = $element->getUser();
            if ($user->getLicence() == false) {

                //suppression de l'inscription
                $em->remove($element);
                $em->flush();
                //envoi du mail pour prevenir de la desinscription
                $subjet = "[Mx Park] - Désinscription entrainement du ".$training->getTrainingDate()->format('d-m-Y');
                $msg = "Bonjour ".$user->getFirstname().",\n".
                "Vous n'avez pas renseigné votre numéro de licence, or elle est necessaire pour participer a l'entrainement du ".$training->getTrainingDate()->format('d-m-Y')."\n".
                "Votre inscription est donc annulée.\n\n".
                "Cordialement,\n".
                "MX Park - Auribail";
                $mailer->sendEmail($user, $subjet, $msg);
            }
        }

        return $this->redirectToRoute('admin_training_users_list', ['training' => $training->getId()]);
    }

    /**
     * @Route("admin/training/list/noLicence/mail/{training}", name="admin_user_no_licence_mail")
     */
    public function sendMailToUserWithoutLicence(Mailer $mailer, EntityManagerInterface $em, Training $training)
    {
        $repoUserTraining = $em->getRepository(UserTraining::class);
        $list = $repoUserTraining->findBy(
            [
                'training' => $training,
            ],
        );

        foreach ($list as $element) {
            $user = $element->getUser();

            //si l'utilisateur n'a pas de licence
            if ($user->getLicence() == false) {
                //envoi de mail pour avertir
                $subjet = "[Mx Park] - WARNING - Entrainement du ".$training->getTrainingDate()->format('d-m-Y');
                $msg = "Bonjour ".$user->getFirstname().",\n".
                "Vous n'avez pas encore renseigné votre numéro de licence, or elle est necessaire pour participer au prochain entrainement du ".$training->getTrainingDate()->format('d-m-Y').
                "\nVeillez la renseignée sous les plus bref delais sous peine de désinscription à la session d'entrainement.\n\n".
                "Cordialement,\n".
                "MX Park - Auribail";
                $mailer->sendEmail($user, $subjet, $msg);
            }
        }

        return $this->redirectToRoute('admin_training_users_list', ['training' => $training->getId()]);
    }



    /**
     * @Route("/admin/training/pdf/{training}",name="admin_training_pdf")
     */
    public function pdfTraining(Training $training, EntityManagerInterface $em, Pdf $snappy)
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

        $filename = 'Entrainement-' . $train->getTrainingDate()->format('d-m-y');

        return new PdfResponse(
            $snappy->getOutputFromHtml($html),
            $filename . '.pdf"'

        );
    }
}
