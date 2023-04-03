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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Event\Log\LogCourseCreateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogCourseEditEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CourseSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private EventDispatcherInterface $eventDispatcher;
    private ObjectManager $om;
    private FileManager $fileManager;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        FileManager $fileManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->fileManager = $fileManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Course::class) => 'preCreate',
            Crud::getEventName('create', 'post', Course::class) => 'postCreate',
            Crud::getEventName('update', 'pre', Course::class) => 'preUpdate',
            Crud::getEventName('update', 'post', Course::class) => 'postUpdate',
            Crud::getEventName('delete', 'pre', Course::class) => 'preDelete',
            Crud::getEventName('delete', 'post', Course::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        /** @var Course $course */
        $course = $event->getObject();

        // If Course is associated to no organization, initializes it with organizations administrated by authenticated user
        // or at last resort with default organizations
        if ($course->getOrganizations()->isEmpty()) {
            if ($user instanceof User && !empty($user->getMainOrganization())) {
                $course->addOrganization($user->getMainOrganization());
            } else {
                // Initializes Course with default organizations if no others organization is found
                /** @var Organization[] $defaultOrganizations */
                $defaultOrganizations = $this->om->getRepository(Organization::class)->findBy(['default' => true]);

                foreach ($defaultOrganizations as $organization) {
                    $course->addOrganization($organization);
                }
            }
        }

        $course->setCreatedAt(new \DateTime());
        $course->setUpdatedAt(new \DateTime());

        if (empty($course->getCreator()) && $user instanceof User) {
            $course->setCreator($user);
        }
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var Course $course */
        $course = $event->getObject();

        if ($course->getPoster()) {
            $this->fileManager->linkFile(Course::class, $course->getUuid(), $course->getPoster());
        }

        if ($course->getThumbnail()) {
            $this->fileManager->linkFile(Course::class, $course->getUuid(), $course->getThumbnail());
        }

        $this->eventDispatcher->dispatch(new LogCourseCreateEvent($course), 'log');
    }

    public function preUpdate(UpdateEvent $event)
    {
        /** @var Course $course */
        $course = $event->getObject();

        $course->setUpdatedAt(new \DateTime());
    }

    public function postUpdate(UpdateEvent $event)
    {
        /** @var Course $course */
        $course = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            Course::class,
            $course->getUuid(),
            $course->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );

        $this->fileManager->updateFile(
            Course::class,
            $course->getUuid(),
            $course->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail'] : null
        );

        $this->eventDispatcher->dispatch(new LogCourseEditEvent($course), 'log');
    }

    public function preDelete(DeleteEvent $event)
    {
        $this->eventDispatcher->dispatch(new LogCourseDeleteEvent($event->getObject()), 'log');
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Course $course */
        $course = $event->getObject();

        if ($course->getPoster()) {
            $this->fileManager->unlinkFile(Course::class, $course->getUuid(), $course->getPoster());
        }

        if ($course->getThumbnail()) {
            $this->fileManager->unlinkFile(Course::class, $course->getUuid(), $course->getThumbnail());
        }
    }
}
