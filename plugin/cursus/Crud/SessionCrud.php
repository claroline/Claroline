<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Event\Log\LogSessionCreateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEditEvent;
use Claroline\CursusBundle\Manager\SessionManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SessionCrud
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var SessionManager */
    private $sessionManager;

    /**
     * SessionCrud constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param SessionManager           $sessionManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        SessionManager $sessionManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->sessionManager = $sessionManager;
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var CourseSession $session */
        $session = $event->getObject();

        // Removes default session flag on all other sessions if this one is the default one
        if ($session->isDefaultSession()) {
            $this->sessionManager->setDefaultSession($session->getCourse(), $session);
        }

        // Creates workspace and roles
        $course = $session->getCourse();
        $workspace = $session->getWorkspace();
        if (empty($workspace) && !empty($course)) {
            $workspace = $course->getWorkspace();

            if (empty($workspace)) {
                $workspace = $this->sessionManager->generateWorkspace($session);
            }
            $session->setWorkspace($workspace);

            $learnerRole = $this->sessionManager->generateRoleForSession(
                $workspace,
                $course->getLearnerRoleName(),
                'learner'
            );
            $session->setLearnerRole($learnerRole);

            $tutorRole = $this->sessionManager->generateRoleForSession(
                $workspace,
                $course->getTutorRoleName(),
                'manager'
            );
            $session->setTutorRole($tutorRole);
        }

        $event = new LogSessionCreateEvent($session);
        $this->eventDispatcher->dispatch($event, 'log');
    }

    public function postUpdate(UpdateEvent $event)
    {
        /** @var CourseSession $session */
        $session = $event->getObject();

        // Removes default session flag on all other sessions if this one is the default one
        if ($session->isDefaultSession()) {
            $this->sessionManager->setDefaultSession($session->getCourse(), $session);
        }

        $event = new LogSessionEditEvent($session);
        $this->eventDispatcher->dispatch($event, 'log');
    }

    public function preDelete(DeleteEvent $event)
    {
        $event = new LogSessionDeleteEvent($event->getObject());
        $this->eventDispatcher->dispatch('log', $event);
    }
}
