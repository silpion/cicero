<?php


namespace Silpion\Cicero\Tools\Composer;

use Silpion\Cicero\Model\Project;
use Silpion\Cicero\Tools\AbstractTool;

/**
 * Runs the composer install.
 *
 * @author Julius Beckmann <beckmann@silpion.de>
 */
class ComposerInstallTool extends AbstractTool
{
    public function run(Project $project)
    {
        // TODO: Check for composer.json or composer.lock file.

        $cmd = 'composer install --prefer-dist --dev --no-interaction';

        $proc = $this->newProcess($cmd, $project->getDir());
        $proc->run();

        $this->setSuccess($proc->isSuccessful());
    }

    public function getName()
    {
        return 'composer_install';
    }
}
 