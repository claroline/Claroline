<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Subscriber\Crud\Planning\AbstractPlannedSubscriber;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Event\Log\LogSessionEventCreateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventEditEvent;
use Claroline\CursusBundle\Manager\EventManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventSubscriber extends AbstractPlannedSubscriber
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var EventManager */
    private $manager;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EventManager $manager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->manager = $manager;
    }

    public static function getPlannedClass(): string
    {
        return Event::class;
    }

    public function preCreate(CreateEvent $event): void
    {
        parent::preCreate($event);

        /** @var Event $object */
        $object = $event->getObject();

        // add event to session and workspace planning
        if ($object->getSession()) {
            $this->planningManager->addToPlanning($object, $object->getSession());

            if ($object->getSession()->getWorkspace()) {
                $this->planningManager->addToPlanning($object, $object->getSession()->getWorkspace());
            }
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        parent::postCreate($event);

        /** @var Event $trainingEvent */
        $trainingEvent = $event->getObject();

        if (Session::REGISTRATION_AUTO === $trainingEvent->getRegistrationType() && !$trainingEvent->isTerminated()) {
            // register session users to the new event

            /** @var SessionUser[] $sessionLearners */
            $sessionLearners = $this->om->getRepository(SessionUser::class)->findBy([
                'session' => $trainingEvent->getSession(),
                'type' => AbstractRegistration::LEARNER,
                'confirmed' => true,
                'validated' => true,
            ]);

            if (!empty($sessionLearners)) {
                $this->manager->addUsers($trainingEvent, array_map(function (SessionUser $sessionUser) {
                    return $sessionUser->getUser();
                }, $sessionLearners), AbstractRegistration::LEARNER);
            }

            $sessionTutors = $this->om->getRepository(SessionUser::class)->findBy([
                'session' => $trainingEvent->getSession(),
                'type' => AbstractRegistration::TUTOR,
                'confirmed' => true,
                'validated' => true,
            ]);

            if (!empty($sessionTutors)) {
                $this->manager->addUsers($trainingEvent, array_map(function (SessionUser $sessionUser) {
                    return $sessionUser->getUser();
                }, $sessionTutors), AbstractRegistration::TUTOR);
            }

            /** @var SessionGroup[] $sessionGroups */
            $sessionGroups = $this->om->getRepository(SessionGroup::class)->findBy([
                'session' => $trainingEvent->getSession(),
            ]);

            if (!empty($sessionGroups)) {
                $this->manager->addGroups($trainingEvent, array_map(function (SessionGroup $sessionGroup) {
                    return $sessionGroup->getGroup();
                }, $sessionGroups));
            }
        }

        $this->eventDispatcher->dispatch(new LogSessionEventCreateEvent($event->getObject()), 'log');
    }

    public function postUpdate(UpdateEvent $event): void
    {
        parent::postUpdate($event);

        $this->eventDispatcher->dispatch(new LogSessionEventEditEvent($event->getObject()), 'log');
    }

    public function preDelete(DeleteEvent $event): void
    {
        $this->eventDispatcher->dispatch(new LogSessionEventDeleteEvent($event->getObject()), 'log');
    }
}
