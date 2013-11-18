<?php

namespace Silpion\Cicero\Tool\Php;

use Psr\Log\LoggerInterface;
use Silpion\Cicero\Logger\LoggableProcess;
use Silpion\Cicero\Model\Project;
use Silpion\Cicero\Tool\ToolInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PHPLocTool implements ToolInterface
{
    public function getConfiguration()
    {
        $node = new ArrayNodeDefinition($this->getName());
        $node->info('Run PHP lines of code analysis');

        $node->children()
                 ->scalarNode('pattern')
                    ->defaultValue('*.php')
                 ->end()
                 ->arrayNode('paths')
                    ->prototype('scalar')->end()
                    ->defaultValue(array('src/'))
                 ->end()
             ->end();

        return $node;
    }

    public function run(Project $project, LoggerInterface $logger = null)
    {
        $config = $project->getToolConfig($this->getName());
        $dir = $project->getDir();

        $paths = array_map(function($path) use ($dir) { return escapeshellarg($dir . '/' . $path); }, $config['paths']);

        // Fix for phploc, somehow needs a created build/log directory.
        $fs = new Filesystem();
        $fs->mkdir($project->getBuildPath().'/log');

        $cmd = 'phploc --log-csv '.escapeshellarg($project->getBuildPath().'/log/phploc.csv').' '.implode(' ', $paths);

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
        return 'php_loc';
    }
}