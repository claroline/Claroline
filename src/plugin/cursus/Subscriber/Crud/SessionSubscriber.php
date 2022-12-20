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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Event\Log\LogSessionCreateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEditEvent;
use Claroline\CursusBundle\Manager\SessionManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var FileManager */
    private $fileManager;
    /** @var SessionManager */
    private $sessionManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        FileManager $fileManager,
        SessionManager $sessionManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileManager = $fileManager;
        $this->sessionManager = $sessionManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Session::class) => 'preCreate',
            Crud::getEventName('create', 'post', Session::class) => 'postCreate',
            Crud::getEventName('update', 'pre', Session::class) => 'preUpdate',
            Crud::getEventName('update', 'post', Session::class) => 'postUpdate',
            Crud::getEventName('delete', 'pre', Session::class) => 'preDelete',
            Crud::getEventName('delete', 'post', Session::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        /** @var Session $session */
        $session = $event->getObject();

        $session->setCreatedAt(new \DateTime());
        $session->setUpdatedAt(new \DateTime());

        if (empty($session->getCreator()) && $user instanceof User) {
            $session->setCreator($user);
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Session $session */
        $session = $event->getObject();

        // Manages files
        if ($session->getPoster()) {
            $this->fileManager->linkFile(Session::class, $session->getUuid(), $session->getPoster());
        }

        if ($session->getThumbnail()) {
            $this->fileManager->linkFile(Session::class, $session->getUuid(), $session->getThumbnail());
        }

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

        $this->eventDispatcher->dispatch(new LogSessionCreateEvent($session), 'log');
    }

    public function preUpdate(UpdateEvent $event): void
    {
        /** @var Session $session */
        $session = $event->getObject();

        $session->setUpdatedAt(new \DateTime());
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Session $session */
        $session = $event->getObject();
        $oldData = $event->getOldData();

        // Removes default session flag on all other sessions if this one is the default one
        if ($session->isDefaultSession()) {
            $this->sessionManager->setDefaultSession($session->getCourse(), $session);
        }

        // Manages files
        $this->fileManager->updateFile(
            Session::class,
            $session->getUuid(),
            $session->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );

        $this->fileManager->updateFile(
            Session::class,
            $session->getUuid(),
            $session->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail'] : null
        );

        $this->eventDispatcher->dispatch(new LogSessionEditEvent($session), 'log');
    }

    public function preDelete(DeleteEvent $event): void
    {
        $this->eventDispatcher->dispatch(new LogSessionDeleteEvent($event->getObject()), 'log');
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Session $session */
        $session = $event->getObject();

        // Manages files
        if ($session->getPoster()) {
            $this->fileManager->unlinkFile(Session::class, $session->getUuid(), $session->getPoster());
        }

        if ($session->getThumbnail()) {
            $this->fileManager->unlinkFile(Session::class, $session->getUuid(), $session->getThumbnail());
        }
    }
}
