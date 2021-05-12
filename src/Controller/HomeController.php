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
        $trainingAdulte = $repoTraining->findOneBy([
            'adult' => 1        
        ], 
        [
            'trainingDate' => 'desc'
        ]);

        $trainingEnfant = $repoTraining->findOneBy([
            'adult' => 0       
        ], [
            'trainingDate' => 'desc'
        ]);

        $repoUserTraining = $em->getRepository(UserTraining::class);
        $userTrainingAdult = $repoUserTraining->findBy([
            'training' => $trainingAdulte
        ]);

        $userTrainingEnfant = $repoUserTraining->findBy([
            'training' => $trainingEnfant
        ]);

        $placeAdult = count($userTrainingAdult);
        $placeEnfant = count($userTrainingEnfant);

        return $this->render('home/index.html.twig', compact('trainingAdulte', 'trainingEnfant', 'placeAdult', 'placeEnfant'));
    }

    /**
     * @Route("/inscription/{user}-{training}", name="home_inscription")
     */
    public function inscription(EntityManagerInterface $em, Training $training, User $user)
    {
          $userTraining = new UserTraining;
          $userTraining->setTraining($training);
          $userTraining->setUser($user);
          $userTraining->setDateRegistration(new DateTime('NOW'));

          $em->persist($userTraining);
          $em->flush();

          return $this->redirectToRoute('home',['_fragment' => 'home-training']);         
          
    }
}