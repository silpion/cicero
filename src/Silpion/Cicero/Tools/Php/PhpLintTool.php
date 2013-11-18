<?php


namespace Silpion\Cicero\Tools\Php;

use Silpion\Cicero\Model\Project;
use Silpion\Cicero\Tools\AbstractTool;
use Symfony\Component\Finder\Finder;

/**
 * Iterates over every *.php file and rund "php -l" on it to check syntax.
 *
 * @author Julius Beckmann <beckmann@silpion.de>
 */
class PhpLintTool extends AbstractTool
{
    public function run(Project $project)
    {
        $config = $project->getPhpConfig();

        // Prefix all paths with current dir.
        $dir = $project->getDir();
        $paths = $this->prefixPaths($dir, $config['paths']);
        // Do not prefix, but do normalize paths. Also stripping the right slash of directories.
        $excludedPaths = array_map(function($path) {return rtrim($path, '/');}, $config['excluded_paths']);

        // Find all PHP files
        $finder = new Finder();
        $finder->name('/\.php$/')->exclude($excludedPaths);

        // Run 'php -l' for each file
        foreach ($finder->in($paths) as $file) {
            $cmd = 'php -l ' . escapeshellarg($file->getRealPath());

            $proc = $this->newProcess($cmd, $project->getDir());
            $proc->run();

            if (!$proc->isSuccessful()) {
                $this->getLogger()->error('Failed: ' . $cmd);
                $this->setSuccess(false);
            }
        }
    }

    public function getName()
    {
        return 'php_lint';
    }
}
 