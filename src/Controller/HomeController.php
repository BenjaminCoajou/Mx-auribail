<?php

namespace App\Controller;

use App\Entity\Training;
use App\Entity\User;
use App\Entity\UserTraining;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(EntityManagerInterface $em): Response
    {

        $repoTraining = $em->getRepository(Training::class);

        $trainingAdult = $repoTraining->findNextTraining('1');
        $trainingChild = $repoTraining->findNextTraining('0');

        $repoUserTraining = $em->getRepository(UserTraining::class);
        $userTrainingAdult = $repoUserTraining->findBy([
            'training' => $trainingAdult
        ]);

        $usertrainingChild = $repoUserTraining->findBy([
            'training' => $trainingChild
        ]);
        $placeAdult = count($userTrainingAdult);
        $placeEnfant = count($usertrainingChild);


        $p = $trainingAdult->getUserTrainings();
        dd($p->toArray());

        return $this->render('home/index.html.twig', compact('trainingAdult', 'trainingChild', 'placeAdult', 'placeEnfant'));
    }

    /**
     * @Route("/inscription/{user}-{training}", name="home_inscription")
     */
    public function inscription(EntityManagerInterface $em, Training $training, User $user)
    {
        $repoUserTraining = $em->getRepository(UserTraining::class);
        $userTraining = $repoUserTraining->findBy(
            ['user' => $user,
            'training' => $training
            ]
        );

        //check if the user is already register for the training
        if(empty($userTraining))
        {
            $userTraining = new UserTraining;
            $userTraining->setTraining($training);
            $userTraining->setUser($user);
            $userTraining->setDateRegistration(new DateTime('NOW'));

            $em->persist($userTraining);
            $em->flush();
        }

       return $this->redirectToRoute('home',['_fragment' => 'home-training']);         
          
    }
}