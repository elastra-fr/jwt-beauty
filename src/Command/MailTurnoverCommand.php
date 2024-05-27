<?php namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Controller\TurnoverCheckController;

#[AsCommand(
    name: 'app:mail-turnover',
    description: 'Envoi un mail aux utilisateurs pour leur chiffre d\'affaires',
)]
class MailTurnoverCommand extends Command
{
    private TurnoverCheckController $turnoverCheckController;

    public function __construct(TurnoverCheckController $turnoverCheckController)
    {
        parent::__construct();
        $this->turnoverCheckController = $turnoverCheckController;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->turnoverCheckController->checkTurnover();

        return Command::SUCCESS;
    }
}