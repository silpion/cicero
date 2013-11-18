<?php

namespace Silpion\Cicero\Tools;

use Silpion\Cicero\Logger\LoggableProcess;
use Silpion\Cicero\Logger\ToolLogger;

abstract class AbstractTool implements ToolInterface
{
    /**
     * @var ToolLogger
     */
    private $logger;

    /**
     * Flag if the Tool ran successfully.
     *
     * @var bool
     */
    private $successfull = true;

    /**
     * Injecting a ToolLogger.
     *
     * @param ToolLogger $logger
     */
    public function setLogger(ToolLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return ToolLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Sets the flag, if the tool ran successfull.
     */
    public function setSuccess($flag)
    {
        $this->successfull = $flag;
    }

    /**
     * Return true if tool ran successfully.
     *
     * @return boolean
     */
    public function isSuccessfull()
    {
        return $this->successfull;
    }

    /**
     * Creates a new Process for given arguments.
     *
     * @param $cmd
     * @param $dir
     * @param int $timeout
     * @param int $idleTimeout
     * @return LoggableProcess
     */
    protected function newProcess($cmd, $dir, $timeout = 900, $idleTimeout = 300)
    {
        $this->logger->debug("Creating Process for command: '$cmd' in directory '$dir'");

        $proc = new LoggableProcess($cmd, $dir);
        $proc->setLogger($this->logger);
        $proc->setTimeout($timeout);
        $proc->setIdleTimeout($idleTimeout);

        return $proc;
    }

    /**
     * Prefixes all paths with the string $prefix.
     *
     * @param $prefix
     * @param array $paths
     * @return array
     */
    protected function prefixPaths($prefix, array $paths) {
        $prefix = rtrim($prefix, '/');
        return array_map(
            function ($path) use ($prefix) {
                return realpath($prefix . '/' . ltrim($path, '/'));
            },
            $paths
        );
    }
}
 