<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;

class MailerController extends AbstractController
{
    /**
     * @Route("/email/{user}", name="email")
     */
    public function sendEmail(MailerInterface $mailer, User $user): Response
    {
        $email = (new Email())
        ->from('mx-auribail@gmail.com')
        ->to($user->getEmail())
        ->subject('Test mailer')
        ->text('Le bon test Vroom Vroom !');

        $mailer->send($email);
        
        return $this->redirectToRoute('home');
        
    }

    /**
     * @Route("/email/cancel/{user}", name="email_cancel")
     */
    public function sendCancelEmail(MailerInterface $mailer, User $user): Response
    {
        $email = (new Email())
        ->from('mx-auribail@gmail.com')
        ->to($user->getEmail())
        ->subject('Entrainement Annulé !')
        ->text("Nous sommes désolé, le prochain entrainement est annulé.");

        $mailer->send($email);
        
        return $this->redirectToRoute('home');
        
    }
}
