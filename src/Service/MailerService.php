<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;

    /**
     * MailerService constructor.
     *
     * @param MailerInterface $mailer
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Fonction pour envoyer un email en fonction des paramètres passés
     *
     * @param string $to
     * @param string $subject
     * @param string $htmlContent
     * @return void
     */
    public function sendEmail(string $to, string $subject, string $htmlContent): void
    {
        $email = (new Email())
            ->from('eldn.dev@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
