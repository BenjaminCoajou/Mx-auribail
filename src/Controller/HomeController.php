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
use Symfony\Component\Security\Core\User\UserInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(EntityManagerInterface $em, ?UserInterface $user)
    {
        $repoTraining = $em->getRepository(Training::class);
        $trainingAdulte = $repoTraining->findOneBy(
            [
                'adult' => 1
            ],
            [
                'trainingDate' => 'desc'
            ]
        );

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
        ], [
            'dateRegistration' => 'desc'
        ]);

        if ($user) {

            $userId = $user->getId();
            $userEmail = $user->getEmail();
            $training = $repoUserTraining->findOneBy([
                'user' => $userId
            ]);
            $list = $repoUserTraining->findAll([
                'training' => $training->getId()
            ]);
            if ($training) {
                $currentTraining = $training->getTraining();
                $slot = $currentTraining->getSlot();
                for ($i = 0; $i < count($list); $i++) {
                    
                    if ($list[$i]->getUser()->getEmail() == $userEmail) {
                        $userPlace = $i + 1;
                    }
                }

                if($userPlace < $slot) {
                    $nameList = "principale";
                    
                }
                else {
                    $nameList ="d'attente";
                }
                
            } else {
                $nameList = "";
            }
        } else {

            $nameList = "";
            $userPlace = "";
        }




        $placeAdult = count($userTrainingAdult);
        $placeEnfant = count($userTrainingEnfant);

        return $this->render('home/index.html.twig', compact('trainingAdulte', 'trainingEnfant', 'placeAdult', 'placeEnfant', 'nameList', 'userPlace'));
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

        return $this->redirectToRoute('home', ['_fragment' => 'home-training']);
    }
}
