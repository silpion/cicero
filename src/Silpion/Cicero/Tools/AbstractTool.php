<?php

namespace Silpion\Cicero\Tools;

use Psr\Log\LoggerInterface;

abstract class AbstractTool implements ToolInterface
{
    private $logger;
    private $successfull = false;

    public function __construct(ToolLogger $logger) {
        $this->logger = $logger;
    }

    public function setSuccess()
    {
        $this->successfull = true;
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
}
 