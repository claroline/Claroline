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
use Claroline\CursusBundle\Manager\SessionManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SessionGroupSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SessionManager $sessionManager
    ) {
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
            $users = [];
            foreach ($sessionGroup->getGroup()->getUsers() as $user) {
                $users[] = $user;
            }

            $this->sessionManager->sendSessionInvitation($session, $users, false);
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
