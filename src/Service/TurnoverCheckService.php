<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Repository\TurnoverRepository;
use App\Service\MailerService;

class TurnoverCheckService
{
    private $entityManager;
    private $userRepository;
    private $turnoverRepository;
    private $mailerService;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, TurnoverRepository $turnoverRepository, MailerService $mailerService)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->turnoverRepository = $turnoverRepository;
        $this->mailerService = $mailerService;
    }

    public function checkAndNotifyUsers()
    {
        $users = $this->userRepository->findAll();
        $previousMonth = new \DateTime('first day of last month');
        $previousMonth->setTime(0, 0, 0);

        foreach ($users as $user) {
            foreach ($user->getSalons() as $salon) {
                $turnover = $this->turnoverRepository->findBySalonAndMonth($salon, $previousMonth);

                if (!$turnover) {
                    $this->sendNotificationEmail($user, $salon);
                }
            }
        }
    }

    private function sendNotificationEmail($user, $salon)
    {
        $subject = "Rappel : Déclarez votre chiffre d'affaires pour le mois précédent";
        $htmlContent = "<p>Bonjour {$user->getFirstName()},</p>
                        <p>Nous avons remarqué que vous n'avez pas déclaré le chiffre d'affaires pour votre salon <b>{$salon->getSalonName()}</b> pour le mois précédent. Veuillez le faire dès que possible.</p>
                        <p>Cordialement,<br>L'équipe</p>";

        $this->mailerService->sendEmail($user->getEmail(), $subject, $htmlContent);
    }
}
