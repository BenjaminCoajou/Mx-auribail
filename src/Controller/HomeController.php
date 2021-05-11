<?php

namespace App\Controller;

use App\Entity\Training;
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

        return $this->render('home/index.html.twig', compact('trainingAdulte', 'trainingEnfant'));
    }
}