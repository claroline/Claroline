<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Event\Log\LogCourseCreateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogCourseEditEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CourseCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
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
        $event = new LogCourseCreateEvent($event->getObject());
        $this->eventDispatcher->dispatch($event, 'log');
    }

    public function preUpdate(UpdateEvent $event)
    {
        /** @var Course $course */
        $course = $event->getObject();

        $course->setUpdatedAt(new \DateTime());
    }

    public function postUpdate(UpdateEvent $event)
    {
        $event = new LogCourseEditEvent($event->getObject());
        $this->eventDispatcher->dispatch($event, 'log');
    }

    public function preDelete(DeleteEvent $event)
    {
        $event = new LogCourseDeleteEvent($event->getObject());
        $this->eventDispatcher->dispatch($event, 'log');
    }
}
