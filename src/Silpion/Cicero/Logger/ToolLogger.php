<?php

namespace Silpion\Cicero\Tools;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class ToolLogger extends AbstractLogger
{
    private $messages = array();

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

        $this->messages[] = array(
            'timestamp' => new \DateTime('now'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'exception' => $exception,
        );
    }
}