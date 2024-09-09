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

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Manager\SessionManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SessionGroupSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly SessionManager $sessionManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, SessionGroup::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, SessionGroup::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, SessionGroup::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var SessionGroup $sessionGroup */
        $sessionGroup = $event->getObject();

        $sessionGroup->setDate(new \DateTime());
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var SessionGroup $sessionGroup */
        $sessionGroup = $event->getObject();
        $session = $sessionGroup->getSession();

        // send invitation if configured
        if ($session->getRegistrationMail()) {
            $groupUsers = $this->om->getRepository(User::class)->findByGroup($sessionGroup->getGroup());

            $this->sessionManager->sendSessionInvitation($session, $groupUsers, false);
        }

        $this->sessionManager->registerGroup($sessionGroup);
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var SessionGroup $sessionGroup */
        $sessionGroup = $event->getObject();

        $this->sessionManager->unregisterGroup($sessionGroup);
    }
}
