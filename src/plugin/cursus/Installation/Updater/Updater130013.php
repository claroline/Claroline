<?php

namespace Claroline\CursusBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\PlanningManager;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\EventGroup;
use Claroline\CursusBundle\Entity\Registration\EventUser;
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
        $this->planEventsForUsers();
        $this->planEventsForGroups();
    }

    /**
     * Adds Events to Session, Workspace plannings.
     */
    private function planEvents()
    {
        $this->om->startFlushSuite();

        /** @var Event[] $events */
        $events = $this->om->getRepository(Event::class)->findAll();
        foreach ($events as $event) {
            if (!empty($event->getSession())) {
                // add to session planning
                $this->planningManager->addToPlanning($event, $event->getSession());

                if (!empty($event->getSession()->getWorkspace())) {
                    // add to workspace planning
                    $this->planningManager->addToPlanning($event, $event->getSession()->getWorkspace());
                }
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * Adds Events to plannings of registered Users.
     */
    private function planEventsForUsers()
    {
        $this->om->startFlushSuite();

        /** @var EventUser[] $eventUsers */
        $eventUsers = $this->om->getRepository(EventUser::class)->findAll();
        foreach ($eventUsers as $eventUser) {
            $this->planningManager->addToPlanning($eventUser->getEvent(), $eventUser->getUser());
        }

        $this->om->endFlushSuite();
    }

    /**
     * Adds Events to plannings of registered Users with groups.
     */
    private function planEventsForGroups()
    {
        $this->om->startFlushSuite();

        /** @var EventGroup[] $eventGroups */
        $eventGroups = $this->om->getRepository(EventGroup::class)->findAll();
        foreach ($eventGroups as $eventGroup) {
            $group = $eventGroup->getGroup();
            foreach ($group->getUsers() as $user) {
                $this->planningManager->addToPlanning($eventGroup->getEvent(), $user);
            }
        }

        $this->om->endFlushSuite();
    }
}
