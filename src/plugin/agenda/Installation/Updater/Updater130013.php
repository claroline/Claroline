<?php

namespace Claroline\AgendaBundle\Installation\Updater;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AgendaBundle\Entity\Task;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\PlanningManager;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130013 extends Updater
{
    /** @var ObjectManager */
    private $om;
    /** @var PlanningManager */
    private $planningManager;

    public function __construct(
        ObjectManager $om,
        PlanningManager $planningManager
    ) {
        $this->om = $om;
        $this->planningManager = $planningManager;
    }

    public function postUpdate()
    {
        $this->planEvents();
        $this->planTasks();
    }

    /**
     * Adds Events to workspace plannings.
     */
    private function planEvents()
    {
        $this->om->startFlushSuite();

        /** @var Event[] $events */
        $events = $this->om->getRepository(Event::class)->findAll();
        foreach ($events as $event) {
            if (!empty($event->getWorkspace())) {
                $this->planningManager->addToPlanning($event, $event->getWorkspace());
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * Adds Tasks to workspace plannings.
     */
    private function planTasks()
    {
        $this->om->startFlushSuite();

        /** @var Task[] $tasks */
        $tasks = $this->om->getRepository(Task::class)->findAll();
        foreach ($tasks as $task) {
            if (!empty($task->getWorkspace())) {
                $this->planningManager->addToPlanning($task, $task->getWorkspace());
            }
        }

        $this->om->endFlushSuite();
    }
}
