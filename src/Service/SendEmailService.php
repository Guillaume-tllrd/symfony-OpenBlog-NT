<?php

namespace App\Service; // App représente le dossier src

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SendEmailService
{

    public function __construct(private MailerInterface $mailer) {}

    public function send(string $from, string $to, string $subject, string $template, array $context): void
    {
        // void=la méthode va retourner rien du tout 
        // on crée le mail :
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("emails/$template.html.twig") //dans le template on lui passe la page twig qu'on a créé dans template/emails
            ->context($context);

        // on envoie le mail en utilisant mailer depuis MailerInterface:
        $this->mailer->send($email);
    }
}
// mtn je vais pouvoir en injectant SendEmailService dans mes controller je vais pouvoir utilisé le send qui va demandé l'expéditeur, le sujet etc. et ca va me créer l'email à condition que le fichier twig soit correct