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

class PHPMDTool implements ToolInterface
{
    public function getConfiguration()
    {
        $node = new ArrayNodeDefinition($this->getName());
        $node->info('Run PHPMD analysis');

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

        $fs = new Filesystem();
        $fs->mkdir($project->getBuildPath().'/log');

        $cmd = 'phpmd '.implode(',', $paths);
        $cmd .= ' xml '.escapeshellarg($dir.'/phpmd.xml');
        $cmd .= ' --reportfile '.escapeshellarg($buildPath.'/log/pmd-phpmd.xml');

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
        return 'php_md';
    }
}