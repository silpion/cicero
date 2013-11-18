<?php

namespace Silpion\Cicero\Cli\Command;

use Silpion\Cicero\Cli\OutputLogger;
use Silpion\Cicero\Cicero;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;

class RunCommand extends Command
{
    protected function configure()
    {
        $this
        ->setName('run')
        ->setDescription('Runs Cicero CI.')
        ->addArgument('directory', InputArgument::REQUIRED, 'The directory that contains the .cicero.yml configuration.')
        ->addOption('build-path', 'bp', InputOption::VALUE_OPTIONAL, 'Path where build should be stored', 'build')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!is_dir($dir = $input->getArgument('directory'))) {
            $output->writeln(sprintf('<error>The directory "%s" does not exist.</error>', $dir));

            return 1;
        }

        $logger = new OutputLogger($output, $input->getOption('verbose'));

        $cicero = new Cicero($logger);
        return $cicero->scrutinize($dir, $input->getOption('build-path'));
    }
}
