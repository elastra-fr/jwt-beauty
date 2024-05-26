<?php




namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Repository\TurnoverRepository;
use App\Service\MailerService;
use App\Entity\User;
use App\Entity\Salon;

class TurnoverCheckService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private TurnoverRepository $turnoverRepository;
    private MailerService $mailerService;

    /**
     * TurnoverCheckService constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param TurnoverRepository $turnoverRepository
     * @param MailerService $mailerService
     */

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, TurnoverRepository $turnoverRepository, MailerService $mailerService)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->turnoverRepository = $turnoverRepository;
        $this->mailerService = $mailerService;
    }
    /**
     * Vérifie si les utilisateurs ont déclaré leur chiffre d'affaires pour le mois précédent et les notifie s'ils ne l'ont pas fait
     * @return void
     */

    public function checkAndNotifyUsers(): void
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

    /**
     * Envoie un email de notification à l'utilisateur
     * @param User $user Utilisateur à notifier
     * @param Salon $salon Salon pour lequel le chiffre d'affaires n'a pas été déclaré
     */
    private function sendNotificationEmail(User $user, Salon $salon)
    {
        $subject = "Rappel : Déclarez votre chiffre d'affaires pour le mois précédent";
        $htmlContent = "<p>Bonjour {$user->getFirstName()},</p>
                        <p>Nous avons remarqué que vous n'avez pas déclaré le chiffre d'affaires pour votre salon <b>{$salon->getSalonName()}</b> pour le mois précédent. Veuillez le faire dès que possible.</p>
                        <p>Cordialement,<br>L'équipe</p>";

        $this->mailerService->sendEmail($user->getEmail(), $subject, $htmlContent);
    }
}
