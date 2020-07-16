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

    /**
     * CourseCrud constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface    $tokenStorage
     * @param ObjectManager            $om
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
    }

    /**
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        /** @var Course $course */
        $course = $event->getObject();
        if ($course->getOrganizations()->isEmpty()) {
            // If Course is associated to no organization, initializes it with organizations administrated by authenticated user
            // or at last resort with default organizations
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            if ('anon.' !== $user && !empty($user->getMainOrganization())) {
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
    }

    public function postCreate(CreateEvent $event)
    {
        $event = new LogCourseCreateEvent($event->getObject());
        $this->eventDispatcher->dispatch($event, 'log');
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
