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
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Manager\SessionManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SessionUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SessionManager $sessionManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, SessionUser::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, SessionUser::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, SessionUser::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, SessionUser::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var SessionUser $sessionUser */
        $sessionUser = $event->getObject();
        $session = $sessionUser->getSession();

        $sessionUser->setDate(new \DateTime());

        if (AbstractRegistration::TUTOR === $sessionUser->getType()) {
            // no validation on tutors
            $sessionUser->setValidated(true);
            $sessionUser->setConfirmed(true);
        } else {
            // set validations for users based on session config
            $sessionUser->setValidated(!$session->getRegistrationValidation() || $sessionUser->isValidated());
            $sessionUser->setConfirmed(!$session->getUserValidation());
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var SessionUser $sessionUser */
        $sessionUser = $event->getObject();
        $session = $sessionUser->getSession();

        // send invitation if configured
        if ($session->getRegistrationMail()) {
            $this->sessionManager->sendSessionInvitation($session, [$sessionUser->getUser()], AbstractRegistration::LEARNER === $sessionUser->getType());
        }

        $this->sessionManager->registerUser($sessionUser);
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var SessionUser $sessionUser */
        $sessionUser = $event->getObject();
        $oldData = $event->getOldData();
        if ((isset($oldData['validated']) && $sessionUser->isValidated() !== $oldData['validated'])
            || (isset($oldData['confirmed']) && $sessionUser->isConfirmed() !== $oldData['confirmed'])) {
            $this->sessionManager->registerUser($sessionUser);
        }
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var SessionUser $sessionUser */
        $sessionUser = $event->getObject();

        $this->sessionManager->unregisterUser($sessionUser);
    }
}
