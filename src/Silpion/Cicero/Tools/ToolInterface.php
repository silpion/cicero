<?php

namespace Silpion\Cicero\Tools;

use Psr\Log\LoggerInterface;
use Silpion\Cicero\Model\Project;

interface ToolInterface
{
    /**
     * Run the tool for the given project.
     *
     * @param Project $project
     * @param LoggerInterface $logger
     * @return mixed
     */
    public function run(Project $project, LoggerInterface $logger = null);

    /**
     * The name of this tool.
     *
     * Should be a lower-case string with "_" as separators.
     *
     * @return string
     */
    public function getName();

    /**
     * Return true if tool ran successfully.
     *
     * @return boolean
     */
    public function isSuccessfull();
}