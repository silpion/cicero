<?php

namespace Silpion\Cicero\Tool\Php;

use Psr\Log\LoggerInterface;
use Silpion\Cicero\Logger\LoggableProcess;
use Silpion\Cicero\Model\Project;
use Silpion\Cicero\Tool\ToolInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SecurityCheckerTool implements ToolInterface
{
    public function getConfiguration()
    {
        $node = new ArrayNodeDefinition($this->getName());
        $node->info('Run Sensio/Security-Checker');

        $node->children()
                 ->scalarNode('composer_lock')
                    ->defaultValue('composer.lock')
                 ->end()
             ->end();

        return $node;
    }

    public function run(Project $project, LoggerInterface $logger = null)
    {
        $config = $project->getToolConfig($this->getName());

        $cmd = 'security-checker security:check '.escapeshellarg($config['composer_lock']);

        $proc = new LoggableProcess($cmd, $project->getDir());
        $proc->setLogger($logger);
        $proc->setTimeout(900);
        $proc->setIdleTimeout(300);
        $proc->run();

        if(!$proc->isSuccessful()) {
            throw new ProcessFailedException($proc);
        }
    }

    public function getName()
    {
        return 'sensiolabs_security_checker';
    }
}