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

class PDependTool implements ToolInterface
{
    public function getConfiguration()
    {
        $node = new ArrayNodeDefinition($this->getName());
        $node->info('Run PDepend analysis');

        $node->children()
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
        $buildPath = $project->getBuildPath();

        $paths = array_map(function($path) use ($dir) { return escapeshellarg($dir . '/' . $path); }, $config['paths']);

        // Fix for pdepend, somehow needs a created build/log directory.
        $fs = new Filesystem();
        $fs->mkdir(array($project->getBuildPath().'/log', $project->getBuildPath().'/pdepend'));

        $cmd = 'pdepend';
        $cmd .= ' --jdepend-xml='.escapeshellarg($buildPath.'/log/jdepend-pdepend.xml');
        $cmd .= ' --jdepend-chart='.escapeshellarg($buildPath.'/pdepend/dependencies.svg');
        $cmd .= ' --overview-pyramid='.escapeshellarg($buildPath.'/pdepend/overview-pyramid.svg');
        $cmd .= ' '.implode(' ', $paths);

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
        return 'php_depend';
    }
}