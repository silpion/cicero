<?php

namespace Silpion\Cicero\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ToolLogger extends AbstractLogger
{
    private $messages = array();

    private $toolName = '';

    private $decoratedLogger = null;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->decoratedLogger = $logger;
    }

    public function setToolName($toolName) {
        $this->toolName = $toolName;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function purgeMessages()
    {
        $this->messages = array();
    }

    public function log($level, $message, array $context = array())
    {
        $exception = isset($context['exception']) ? $context['exception'] : null;

        if($this->toolName && $message) {
            $message = '    ['.$this->toolName.']  ' . $message;
        }

        $this->messages[] = array(
            'timestamp' => new \DateTime('now'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'exception' => $exception,
        );

        if($this->decoratedLogger) {
            $this->decoratedLogger->log($level, $message, $context);
        }
    }
}