<?php

namespace Claroline\SchedulerBundle\Event;

use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Symfony\Contracts\EventDispatcher\Event;

class ExecuteScheduledTaskEvent extends Event
{
    /** @var ScheduledTask */
    private $task;

    /** @var string */
    private $status = ScheduledTask::SUCCESS;

    private $errors;

    public function __construct(ScheduledTask $task)
    {
        $this->task = $task;
    }

    public function getTask(): ScheduledTask
    {
        return $this->task;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }
}
