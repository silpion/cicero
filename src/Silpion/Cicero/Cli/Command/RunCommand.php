<?php

namespace Silpion\Cicero\Cli\Command;

use Silpion\Cicero\Cli\OutputLogger;
use Silpion\Cicero\Cicero;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;

/**
 * Cicero RunCommand.
 *
 * @author Julius Beckmann <beckmann@silpion.de>
 */
class RunCommand extends Command
{
    protected function configure()
    {
        $this
        ->setName('run')
        ->setDescription('Runs Cicero CI.')
        ->addArgument('directory', InputArgument::REQUIRED, 'The directory that contains the '.Cicero::CONFIG_NAME.' configuration.')
        ->addOption('build-path', 'bp', InputOption::VALUE_OPTIONAL, 'Path where build should be stored', 'build')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getArgument('directory');

        if (!is_dir($dir)) {
            $output->writeln(sprintf('<error>The directory "%s" does not exist.</error>', $dir));

            return 1;
        }

        $logger = new OutputLogger($output, $input->getOption('verbose'));

        $cicero = new Cicero($logger);
        return (int)$cicero->run($dir, $input->getOption('build-path'));
    }
}
