<?php

namespace Silpion\Cicero\Tool\Php;

use Psr\Log\LoggerInterface;
use Silpion\Cicero\Logger\LoggableProcess;
use Silpion\Cicero\Model\Project;
use Silpion\Cicero\Tool\ToolInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PHPLintTool implements ToolInterface
{
    public function getConfiguration()
    {
        $node = new ArrayNodeDefinition($this->getName());
        $node->info('Run PHP lint check');

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

        $paths = array_map(function($path) use ($dir) { return $dir . '/' . $path; }, $config['paths']);

        $finder = new Finder();
        $finder->name($config['pattern']);
        foreach ($finder->in($paths) as $file) {
            $cmd = 'php -l '.escapeshellarg($file->getRealPath());

            $proc = new LoggableProcess($cmd, $project->getDir());
            $proc->setLogger($logger);
            $proc->setTimeout(900);
            $proc->setIdleTimeout(300);
            $proc->run();

            if(!$proc->isSuccessful()) {
                throw new ProcessFailedException($proc);
            }
        }
    }

    public function getName()
    {
        return 'php_lint';
    }
}