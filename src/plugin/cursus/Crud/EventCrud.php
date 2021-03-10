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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\PlanningManager;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Event\Log\LogSessionEventCreateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventEditEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var PlanningManager */
    private $planningManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        PlanningManager $planningManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->planningManager = $planningManager;
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var Event $object */
        $object = $event->getObject();

        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User && empty($object->getCreator())) {
            $object->setCreator($user);
        }

        $object->setCreatedAt(new \DateTime());
        $object->setUpdatedAt(new \DateTime());
    }

    public function preUpdate(UpdateEvent $event)
    {
        /** @var Event $object */
        $object = $event->getObject();

        $object->setUpdatedAt(new \DateTime());
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var Event $object */
        $object = $event->getObject();

        // add event to session and workspace planning
        if ($object->getSession()) {
            $this->planningManager->addToPlanning($object, $object->getSession());

            if ($object->getSession()->getWorkspace()) {
                $this->planningManager->addToPlanning($object, $object->getSession()->getWorkspace());
            }
        }

        $this->eventDispatcher->dispatch(new LogSessionEventCreateEvent($event->getObject()), 'log');
    }

    public function postUpdate(UpdateEvent $event)
    {
        $this->eventDispatcher->dispatch(new LogSessionEventEditEvent($event->getObject()), 'log');
    }

    public function preDelete(DeleteEvent $event)
    {
        $this->eventDispatcher->dispatch(new LogSessionEventDeleteEvent($event->getObject()), 'log');
    }
}
