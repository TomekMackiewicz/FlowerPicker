<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\FlowerPickerService;

// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'app:flower-picker',
    description: 'Picks three random flowers'
)]
class FlowerPickerCommand extends Command
{
    public function __construct(private readonly FlowerPickerService $flowerPickerService)
    {
        //$this->userManager = $userManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to pick three random flowers...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->flowerPickerService->fetchWebsiteInformation();
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}
