<?php
namespace App\Command;

use App\Controller\TurnoverCheckController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckTurnoverCommand extends Command
{
    protected static $defaultName = 'app:check-turnover';

    private $turnoverCheckController;

    public function __construct(TurnoverCheckController $turnoverCheckController)
    {
        $this->turnoverCheckController = $turnoverCheckController;

        parent::__construct();
    }

    protected function configure(): void
    {
        // Définition de la description de la commande
        $this->setDescription('Check turnover command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Exécution de la méthode pour vérifier le chiffre d'affaires
        $this->turnoverCheckController->checkTurnover();

        // Retourne le code de succès de la commande
        return Command::SUCCESS;
    }
}
