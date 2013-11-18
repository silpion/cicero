<?php

namespace Silpion\Cicero\Tool\Php;

use Psr\Log\LoggerInterface;
use Silpion\Cicero\Logger\LoggableProcess;
use Silpion\Cicero\Model\Project;
use Silpion\Cicero\Tool\ToolInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PHPUnitTool implements ToolInterface
{
    public function getConfiguration()
    {
        $node = new ArrayNodeDefinition($this->getName());
        $node->info('Run PHPUnit');

        $node->children()
                 ->scalarNode('config')
                    ->defaultValue('phpunit.xml.dist')
                 ->end()
             ->end();

        return $node;
    }

    public function run(Project $project, LoggerInterface $logger = null)
    {
        $config = $project->getToolConfig($this->getName());
        $buildPath = $project->getBuildPath();

        $cmd = 'phpunit -c '.escapeshellarg($config['config']);
        $cmd .= ' --coverage-text --coverage-clover '.$buildPath.'/log/clover-phpunit.xml';
        $cmd .= ' --coverage-html '.$buildPath.'/coverage-phpunit --log-junit '.$buildPath.'/log/junit-phpunit.xml';

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
        return 'php_unit';
    }
}