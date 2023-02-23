<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\FlowerPickerService;

#[AsCommand(
    name: 'app:flower-picker',
    description: 'Picks three random flowers'
)]
class FlowerPickerCommand extends Command
{
    public function __construct(private readonly FlowerPickerService $flowerPickerService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to pick three random flowers...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(['Downloading flower images...', '============', '']);
        $response = $this->flowerPickerService->importImages();
        if (true !== $response) {
            $output->writeln(
                ['An error occured, see var/log/log.err file for more details.', '============', '']
            );
            return Command::FAILURE;
        }
        $output->writeln(['Images succesfully downloaded :)', '============', '']);

        return Command::SUCCESS;
    }
}
