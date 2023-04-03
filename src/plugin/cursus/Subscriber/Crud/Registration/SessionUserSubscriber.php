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
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Event\Log\LogSessionUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionUserUnregistrationEvent;
use Claroline\CursusBundle\Manager\SessionManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SessionUserSubscriber implements EventSubscriberInterface
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
            Crud::getEventName('create', 'pre', SessionUser::class) => 'preCreate',
            Crud::getEventName('create', 'post', SessionUser::class) => 'postCreate',
            Crud::getEventName('update', 'post', SessionUser::class) => 'postUpdate',
            Crud::getEventName('delete', 'post', SessionUser::class) => 'postDelete',
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

        $this->eventDispatcher->dispatch(new LogSessionUserRegistrationEvent($event->getObject()), 'log');
    }

    public function postUpdate(UpdateEvent $event)
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

        $this->eventDispatcher->dispatch(new LogSessionUserUnregistrationEvent($sessionUser), 'log');
    }
}
