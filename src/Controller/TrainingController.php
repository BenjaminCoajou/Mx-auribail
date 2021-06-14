<?php

namespace App\Controller;

use App\Entity\Training;
use App\Entity\User;
use App\Entity\UserTraining;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class TrainingController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
       $this->security = $security;
    }

    /**
     * @Route("/history/{user}", name="history")
     */
    public function history(EntityManagerInterface $em, User $user): Response
    {
        $repository = $em->getRepository(UserTraining::class);

        $userTraining = $repository->findBy([
            'user' => $user
            ]
        );

        return $this->render('training/history.html.twig', compact('userTraining'));
    }
}
