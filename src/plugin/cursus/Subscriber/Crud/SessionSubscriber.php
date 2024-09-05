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
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\SessionManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FileManager $fileManager,
        private readonly SessionManager $sessionManager,
        private readonly Crud $crud,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, Session::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Session::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::PRE_UPDATE, Session::class) => 'preUpdate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Session::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Session::class) => 'postDelete',
            CrudEvents::getEventName(CrudEvents::PRE_COPY, Session::class) => 'preCopy',
            CrudEvents::getEventName(CrudEvents::POST_COPY, Session::class) => 'postCopy',
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
            // link the session to the configured workspace on the parent training
            $workspace = $course->getWorkspace();
            if (!empty($workspace)) {
                // The parent training as a workspace linked to it
                if ($workspace->isModel()) {
                    // The linked workspace is a model, we need to generate a new workspace from it for the new session
                    $workspace = $this->sessionManager->generateWorkspace($session);
                }

                // Link the session the workspace
                $session->setWorkspace($workspace);

                $learnerRole = $this->sessionManager->generateRoleForSession(
                    $workspace,
                    $course->getLearnerRole(),
                    'learner'
                );
                $session->setLearnerRole($learnerRole);

                $tutorRole = $this->sessionManager->generateRoleForSession(
                    $workspace,
                    $course->getTutorRole(),
                    'manager'
                );
                $session->setTutorRole($tutorRole);
            }
        }
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

    public function preCopy(CopyEvent $event): void
    {
        /** @var Course $course */
        $course = $event->getExtra()['parent'];

        /** @var Session $copy */
        $copy = $event->getCopy();

        $copy->setCourse($course);
        $copy->setCreatedAt(new \DateTime());
        $copy->setUpdatedAt(new \DateTime());

        $copyName = $this->sessionManager->getCopyName($copy->getName());
        $copy->setName($copyName);
        $copy->setCode($copyName);
    }

    public function postCopy(CopyEvent $event): void
    {
        /** @var Session $original */
        $original = $event->getObject();

        /** @var Session $copy */
        $copy = $event->getCopy();

        foreach ($original->getEvents() as $seance) {
            $this->crud->copy($seance, [], ['parent' => $copy]);
        }

        if ($copy->getPoster()) {
            $this->fileManager->linkFile(Session::class, $copy->getUuid(), $copy->getPoster());
        }

        if ($copy->getThumbnail()) {
            $this->fileManager->linkFile(Session::class, $copy->getUuid(), $copy->getThumbnail());
        }
    }
}
