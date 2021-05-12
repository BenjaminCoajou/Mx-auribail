<?php

namespace App\Service;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Mailer
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail($user,$subject,$msg)
    {
        $mail = (new Email())
        ->from('mx-auribail@gmail.com')
        ->to($user->getEmail())
        ->subject($subject)
        ->text($msg);

        $this->mailer->send($mail);
    }

}