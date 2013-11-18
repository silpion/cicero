<?php

namespace Silpion\Cicero\Cli;

use Silpion\Cicero\Cli\Command\RunCommand;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('Silpion/Cicero', '0.1');
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new RunCommand();

        return $commands;
    }
}