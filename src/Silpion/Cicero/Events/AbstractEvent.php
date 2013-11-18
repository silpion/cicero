<?php


namespace Silpion\Cicero\Events;

use Symfony\Component\EventDispatcher\Event;

use Silpion\Cicero\Model\Project;

abstract class AbstractEvent extends Event
{
    private $project;

    private $output = array();

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function setOutput($toolName, array $messages)
    {
        $this->output[$toolName] = $messages;
    }

    public function getOutput() {
        return $this->output;
    }
}
 