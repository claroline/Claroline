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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Manager\CourseManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CourseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly FileManager $fileManager,
        private readonly Crud $crud,
        private readonly CourseManager $manager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, Course::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Course::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::PRE_UPDATE, Course::class) => 'preUpdate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Course::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Course::class) => 'postDelete',
            CrudEvents::getEventName(CrudEvents::PRE_COPY, Course::class) => 'preCopy',
            CrudEvents::getEventName(CrudEvents::POST_COPY, Course::class) => 'postCopy',
        ];
    }

    public function preCreate(CreateEvent $event): void
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

    public function postCreate(CreateEvent $event): void
    {
        /** @var Course $course */
        $course = $event->getObject();

        if ($course->getPoster()) {
            $this->fileManager->linkFile(Course::class, $course->getUuid(), $course->getPoster());
        }

        if ($course->getThumbnail()) {
            $this->fileManager->linkFile(Course::class, $course->getUuid(), $course->getThumbnail());
        }
    }

    public function preUpdate(UpdateEvent $event): void
    {
        /** @var Course $course */
        $course = $event->getObject();

        $course->setUpdatedAt(new \DateTime());
    }

    public function postUpdate(UpdateEvent $event): void
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

    public function preCopy(CopyEvent $event): void
    {
        /** @var Course $original */
        $original = $event->getObject();

        /** @var Course $copy */
        $copy = $event->getCopy();

        $copy->refreshUuid();
        $copy->setCreatedAt(new \DateTime());
        $copy->setUpdatedAt(new \DateTime());
        $copyName = $this->manager->getCopyName($original->getName());
        $copy->setSlug($copyName);
        $copy->setName($copyName);
        $copy->setCode($copyName);

        foreach ($original->getSessions() as $session) {
            $this->crud->copy($session, [], ['parent' => $copy]);
        }
    }

    public function postCopy(CopyEvent $event): void
    {
        /** @var Course $course */
        $course = $event->getCopy();

        if ($course->getPoster()) {
            $this->fileManager->linkFile(Course::class, $course->getUuid(), $course->getPoster());
        }

        if ($course->getThumbnail()) {
            $this->fileManager->linkFile(Course::class, $course->getUuid(), $course->getThumbnail());
        }

        if ($course->getWorkspace() && !$course->getWorkspace()->isModel()) {
            $course->setWorkspace(null);
        }
    }
}
