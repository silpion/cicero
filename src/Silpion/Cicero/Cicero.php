<?php

namespace Silpion\Cicero;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Silpion\Cicero\Events\ComposerEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Yaml;

use Silpion\Cicero\Logger\LoggableProcess;
use Silpion\Cicero\Model\Project;
use Silpion\Cicero\Events\CiceroEvents;
use Silpion\Cicero\Events\AbstractEvent;

/**
 * The Cicero CI tool.
 */
class Cicero
{
    const CONFIG_NAME = '.cicero.yml';

    private $logger;

    private $eventDispatcher;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ? : new NullLogger();
        $this->eventDispatcher = new EventDispatcher();

        $this->registerDefaultListeners();
    }

    protected function registerDefaultListeners()
    {

    }

    public function getEventDispatcher()
    {
        $this->eventDispatcher;
    }

    public function scrutinize($dir, $buildPath)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }
        $dir = realpath($dir);

        if (!is_file($dir . '/' . static::CONFIG_NAME)) {
            throw new \Exception("No config file found.");
        }

        $rawConfig = Yaml::parse(file_get_contents($dir . '/' . static::CONFIG_NAME));

        //var_dump($rawConfig);

        $config = $this->getConfiguration()->process($rawConfig);

        //var_dump($config); die();

        $project = new Project($dir, $buildPath, $config);

        $this->runComposer($project);

       // $this->runPhp($project);
/*
        if ($project->isFailed()) {
            $this->logger->warning("---------------------------------\nRun failed! Check output for errors.\n");
        }
*/
        return $project->isFailed();
    }

    private function getConfiguration()
    {
        return new Configuration();
    }

    private function outputEventResult(AbstractEvent $event) {
        $output = $event->getOutput();

        foreach($output as $toolName => $messages) {
            $line = "[$toolName] ".$messages['message'];
            $this->logger->notice($line);
        }
    }

    private function runComposer(Project $project)
    {
        $config = $project->getComposerConfig();
        if (!$config['enabled']) {
            $this->logger->info("Composer not enabled - skipping.");
            return;
        }

        $event = new ComposerEvent($project);
        $this->eventDispatcher->dispatch(CiceroEvents::COMPOSER, $event);

        $this->outputEventResult($event);

        /*

        $this->logger->info("---------------------------------------------------------------\n");
        $this->logger->info('--- Executing composer' . "\n");

        $cmd = 'composer install --prefer-dist --dev --no-interaction';

        var_dump($cmd);

        $proc = new LoggableProcess($cmd, $project->getDir());
        $proc->setTimeout(900);
        $proc->setIdleTimeout(300);
        $proc->setLogger($this->logger);
        $proc->run();

        if (!$proc->isSuccessful()) {
            $project->setFailed();
        }

        $cmd = 'security-checker security:check composer.lock';

        var_dump($cmd);

        $proc = new LoggableProcess($cmd, $project->getDir());
        $proc->setLogger($this->logger);
        $proc->setTimeout(900);
        $proc->setIdleTimeout(300);
        $proc->run();

        if (!$proc->isSuccessful()) {
            $project->setFailed();
        }
        */
    }

    private function runPhp(Project $project)
    {
        $config = $project->getPhpConfig();
        if (!$config['enabled']) {
            return;
        }
        $this->logger->info("---------------------------------------------------------------\n");
        $this->logger->info('--- Executing PHP Analysis' . "\n");

        $this->logger->info("PHP-Lint:\n");
        $this->runPhpLint($project);

        $this->logger->info("PHPLoc:\n");
        $this->runPhpLoc($project);

        $this->logger->info("PHP_Depend\n");
        $this->runPhpDepend($project);

        $this->logger->info("PHPMP\n");
        $this->runPhpMessupDetection($project);

        $this->logger->info("PHP_Checkstyle\n");
        $this->runPhpCheckstyle($project);

        $this->logger->info("PHP-CopyPasteDetection\n");
        $this->runPhpCopyPasteDetection($project);

        $this->logger->info("PHPUnit\n");
        $this->runPhpUnit($project);
    }

    private function runPhpLint(Project $project)
    {
        $config = $project->getPhpConfig();
        $dir = $project->getDir();
        $paths = array_map(
            function ($path) use ($dir) {
                return $dir . '/' . $path;
            },
            $config['paths']
        );

        $finder = new Finder();
        $finder->name($config['pattern']);
        $finder->exclude($config['excluded_paths']);
        foreach ($finder->in($paths) as $file) {
            $cmd = 'php -l ' . escapeshellarg($file->getRealPath());

            var_dump($cmd);

            $proc = new LoggableProcess($cmd, $project->getDir());
            $proc->setLogger($this->logger);
            $proc->setTimeout(900);
            $proc->setIdleTimeout(300);
            $proc->run();

            if (!$proc->isSuccessful()) {
                $project->setFailed();
            }
        }
    }

    private function runPhpLoc(Project $project)
    {
        $config = $project->getPhpConfig();

        $paths = array_map('escapeshellarg', $config['paths']);
        $excludedPaths = array_map('escapeshellarg', $config['excluded_paths']);

        $fs = new Filesystem();
        $fs->mkdir($project->getBuildPath() . '/logs');

        $cmd = 'phploc --progress --names ' . escapeshellarg($config['pattern']) . ' --log-csv ' . escapeshellarg(
            $project->getBuildPath() . '/logs/phploc.csv'
        );
        foreach ($excludedPaths as $path) {
            $cmd .= ' --exclude ' . $path;
        }
        $cmd .= ' ' . implode(' ', $paths);

        var_dump($cmd);

        $proc = new LoggableProcess($cmd, $project->getDir());
        $proc->setLogger($this->logger);
        $proc->setTimeout(900);
        $proc->setIdleTimeout(300);
        $proc->run();

        if (!$proc->isSuccessful()) {
            throw new ProcessFailedException($proc);
        }
    }

    private function runPhpDepend(Project $project)
    {
        $config = $project->getPhpConfig();

        $dir = $project->getDir();
        $paths = array_map(
            function ($path) use ($dir) {
                return escapeshellarg($dir . '/' . $path);
            },
            $config['paths']
        );
        $excludedPaths = array_map(
            function ($path) use ($dir) {
                return $dir . '/' . $path;
            },
            $config['excluded_paths']
        );
        $buildPath = $project->getBuildPath();

        $fs = new Filesystem();
        $fs->mkdir(array($project->getBuildPath() . '/logs', $project->getBuildPath() . '/pdepend'));

        $cmd = 'pdepend';
        $cmd .= ' --suffix=' . escapeshellarg(str_ireplace('*.', '', $config['pattern']));
        if ($excludedPaths) {
            $cmd .= ' --ignore=' . escapeshellarg(implode(',', $excludedPaths));
        }
        $cmd .= ' --jdepend-xml=' . escapeshellarg($buildPath . '/logs/jdepend-pdepend.xml');
        $cmd .= ' --jdepend-chart=' . escapeshellarg($buildPath . '/pdepend/dependencies.svg');
        $cmd .= ' --overview-pyramid=' . escapeshellarg($buildPath . '/pdepend/overview-pyramid.svg');
        $cmd .= ' ' . implode(' ', $paths);

        var_dump($cmd);

        $proc = new LoggableProcess($cmd, $project->getDir());
        $proc->setLogger($this->logger);
        $proc->setTimeout(900);
        $proc->setIdleTimeout(300);
        $proc->run();

        if (!$proc->isSuccessful()) {
            throw new ProcessFailedException($proc);
        }
    }

    private function runPhpMessupDetection(Project $project)
    {
        // TODO!
        // Need to change CWD because of BUGS!
        $this->logger->info("TODO: Implement PMD\n");
    }

    private function runPhpCheckstyle(Project $project)
    {
        $config = $project->getPhpConfig();

        $dir = $project->getDir();
        $buildPath = $project->getBuildPath();

        $paths = array_map(
            function ($path) use ($dir) {
                return escapeshellarg($dir . '/' . $path);
            },
            $config['paths']
        );
        $excludedPaths = array_map(
            function ($path) use ($dir) {
                return $dir . '/' . $path;
            },
            $config['excluded_paths']
        );

        $fs = new Filesystem();
        $fs->mkdir($project->getBuildPath() . '/logs');

        $cmd = 'phpcs -p --report=checkstyle --standard=PSR2';
        $cmd .= ' --report-file=' . escapeshellarg($buildPath . '/logs/checkstyle-phpcs.xml');
        if ($excludedPaths) {
            $cmd .= ' --ignore=' . escapeshellarg(implode(',', $excludedPaths));
        }
        $cmd .= ' ' . implode(' ', $paths);

        var_dump($cmd);

        $proc = new LoggableProcess($cmd, $project->getDir());
        $proc->setLogger($this->logger);
        $proc->setTimeout(900);
        $proc->setIdleTimeout(300);
        $exitCode = $proc->run();

        if ($proc->getExitCode() > 1) {
            throw new ProcessFailedException($proc);
        }
    }

    private function runPhpCopyPasteDetection(Project $project)
    {
        $config = $project->getPhpConfig();
        $buildPath = $project->getBuildPath();

        $paths = array_map('escapeshellarg', $config['paths']);
        $excludedPaths = array_map('escapeshellarg', $config['excluded_paths']);

        $fs = new Filesystem();
        $fs->mkdir($project->getBuildPath() . '/logs');

        $cmd = 'phpcpd  --log-pmd ' . escapeshellarg($buildPath . '/logs/pmd-phpcpd.xml');
        foreach ($excludedPaths as $path) {
            $cmd .= ' --exclude ' . $path;
        }
        $cmd .= ' ' . implode(' ', $paths);

        var_dump($cmd);

        $proc = new LoggableProcess($cmd, $project->getDir());
        $proc->setLogger($this->logger);
        $proc->setTimeout(900);
        $proc->setIdleTimeout(300);
        $proc->run();

        if (!$proc->isSuccessful()) {
            $project->setFailed();
        }
    }

    private function runPhpUnit(Project $project)
    {
        $buildPath = $project->getBuildPath();
        $dir = $project->getDir();

        // Search for a default phpunit config file.
        $expectedConfigs = array(
            'phpunit.xml',
            'phpunit.xml.dist',
            'app/phpunit.xml',
            'app/phpunit.xml.dist',
        );

        $configPath = null;

        foreach ($expectedConfigs as $path) {
            if (file_exists($dir . '/' . $path)) {
                $this->logger->notice("Using default config file: $path\n");
                $configPath = $dir . '/' . $path;
                break;
            }
        }

        if (!$configPath) {
            $this->logger->notice("No default config file found.\n");
            return;
        }

        $cmd = 'phpunit -c ' . escapeshellarg($configPath);
        $cmd .= ' --coverage-text --coverage-clover ' . escapeshellarg($buildPath . '/logs/clover-phpunit.xml');
        $cmd .= ' --coverage-html ' . escapeshellarg($buildPath . '/coverage-phpunit');
        $cmd .= ' --log-junit ' . escapeshellarg($buildPath . '/logs/junit-phpunit.xml');

        var_dump($cmd);

        $proc = new LoggableProcess($cmd, $project->getDir());
        $proc->setLogger($this->logger);
        $proc->setTimeout(900);
        $proc->setIdleTimeout(300);
        $proc->run();

        if (!$proc->isSuccessful()) {
            throw new ProcessFailedException($proc);
        }
    }
}