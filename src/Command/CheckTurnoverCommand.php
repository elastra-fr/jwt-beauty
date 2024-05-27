<?php
namespace App\Command;

use App\Controller\TurnoverCheckController;
use App\Service\TurnoverCheckService;
use App\Service\JsonResponseNormalizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckTurnoverCommand extends Command
{
    protected static $defaultName = 'app:check-turnover';

    private $turnoverCheckController;

    public function __construct(TurnoverCheckService $turnoverCheckService, JsonResponseNormalizer $jsonResponseNormalizer)
    {
        $this->turnoverCheckController = new TurnoverCheckController($turnoverCheckService, $jsonResponseNormalizer);

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->turnoverCheckController->checkTurnover();

        return Command::SUCCESS;
    }
}