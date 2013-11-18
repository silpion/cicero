<?php

namespace Silpion\Cicero\Tools;

use Silpion\Cicero\Model\Project;

interface ToolInterface
{
    /**
     * Run the tool for the given project.
     *
     * @param Project $project
     * @return
     */
    public function run(Project $project);

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