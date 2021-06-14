<?php

namespace App\Controller;

use App\Entity\Training;
use App\Entity\User;
use App\Entity\UserTraining;
use App\Service\Mailer;
use DateTime;
use DateTimeZone;
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

        $trainingAdult = $repoTraining->findNextTraining('1');
        $trainingChild = $repoTraining->findNextTraining('0');
        

        $repoUserTraining = $em->getRepository(USerTraining::class);
        $adults = $repoUserTraining->findby(['training'=>$trainingAdult]);
        $childs = $repoUserTraining->findby(['training'=>$trainingChild]);

        $listIdAdult = [];
        foreach($adults as $adult)
        {
            $listIdAdult[] = $adult->getUser()->getId();
        }
        $listIdChild = [];
        foreach($childs as $child)
        {
            $listIdChild[] = $child->getUser()->getId();
        }

        $repoUserTraining = $em->getRepository(UserTraining::class);
        $userTrainingAdult = $repoUserTraining->findBy([
            'training' => $trainingAdult
        ]);

        $usertrainingChild = $repoUserTraining->findBy([
            'training' => $trainingChild
        ]);
        
        //Initialiser les variables à renvoyer dans la vue
        $count = 1;
        $age = null;
        $nameList = false;
        $userPlace = false;
        // si un utilisateur est connecté
        if ($user) {
            $age = $user->getBirthday()->diff(new \DateTime())->y;

            if($age < 18)
            {
                $training = $trainingChild;
            }else{
                $training = $trainingAdult;
            }

            $currentUserTraining = $repoUserTraining->findby([
                'training' => $training,
                'user' => $user
            ]);


            if(!empty($currentUserTraining)){
                $users =$repoUserTraining->findBy([
                    'training'=> $training,
                ],
                [
                    'dateRegistration'=> 'ASC'
                ]
                );
                foreach($users as $u)
                { 
                    if($user->getId() !== $u->getUser()->getId())
                    {
                        $count++;    
                    }
                    else{
                        break;
                    }
                }

                $slot = $training->getSlot();
                if($count <= $slot) {
                    $nameList = "principale";                
                }
                else {
                    $nameList ="d'attente";
                    $count = $count - $slot;
                }
                $userPlace = $count;
            }
        } 


        $placeAdult = count($userTrainingAdult);
        $placeEnfant = count($usertrainingChild);

        return $this->render('home/index.html.twig', compact('trainingAdult', 'trainingChild', 'placeAdult', 'placeEnfant','listIdAdult','listIdChild','nameList', 'userPlace','age'));
    }

    /**
     * @Route("/inscription/{user}-{training}", name="home_inscription")
     */
    public function inscription(EntityManagerInterface $em, Training $training, User $user, Mailer $mailer)
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
            $userTraining->setDateRegistration(new DateTime('NOW', new DateTimeZone('Europe/Paris')));

            $em->persist($userTraining);
            $em->flush();

            //Send mail when the user get register
            $subjet = "[Mx Park] - INSCRIPTION - Entrainement du ".$training->getTrainingDate()->format('d-m-Y');
            $msg = "Bonjour ".$user->getFirstname().",\n".
            "Vous êtes bien inscrit au prochain entrainement du ".$training->getTrainingDate()->format('d-m-Y').
            "\nVeillez renseigner votre numéro de licence avant si ce n'est pas encore le cas, elle est necessaire a la participation.\n\n".
            "Cordialement,\n".
            "MX Park - Auribail";
            $mailer->sendEmail($user, $subjet, $msg);
        }

       return $this->redirectToRoute('home',['_fragment' => 'home-training']);         
          
    }


    /**
     * @Route("/unsub/{user}-{training}", name="home_unsub")
     */
    public function unsubscription(EntityManagerInterface $em, Training $training, User $user)
    {
        $repoUserTraining = $em->getRepository(UserTraining::class);
        $userTraining = $repoUserTraining->findOneBy(
            ['user' => $user,
            'training' => $training
            ]
        );
        $em->remove($userTraining);
        $em->flush();

       return $this->redirectToRoute('home',['_fragment' => 'home-training']);         
    }
    
    /**
     * @Route("/dev", name="home_dev")
     */
    public function konamiCode(): Response
    {
        return $this->render('home/konami.html.twig', []);
    }
}
