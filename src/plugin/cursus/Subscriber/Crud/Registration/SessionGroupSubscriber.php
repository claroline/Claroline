<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Subscriber\Crud\Registration;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Event\Log\LogSessionGroupRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionGroupUnregistrationEvent;
use Claroline\CursusBundle\Manager\SessionManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SessionGroupSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private SessionManager $sessionManager;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        SessionManager $sessionManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->sessionManager = $sessionManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', SessionGroup::class) => 'preCreate',
            Crud::getEventName('create', 'post', SessionGroup::class) => 'postCreate',
            Crud::getEventName('delete', 'post', SessionGroup::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var SessionGroup $sessionUser */
        $sessionUser = $event->getObject();

        $sessionUser->setDate(new \DateTime());
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var SessionGroup $sessionGroup */
        $sessionGroup = $event->getObject();
        $session = $sessionGroup->getSession();

        // send invitation if configured
        if ($session->getRegistrationMail()) {
            $users = [];
            foreach ($sessionGroup->getGroup()->getUsers() as $user) {
                $users[] = $user;
            }

            $this->sessionManager->sendSessionInvitation($session, $users, false);
        }

        $this->sessionManager->registerGroup($sessionGroup);

        $this->eventDispatcher->dispatch(new LogSessionGroupRegistrationEvent($event->getObject()), 'log');
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var SessionGroup $sessionGroup */
        $sessionGroup = $event->getObject();

        $this->sessionManager->unregisterGroup($sessionGroup);

        $this->eventDispatcher->dispatch(new LogSessionGroupUnregistrationEvent($sessionGroup), 'log');
    }
}
