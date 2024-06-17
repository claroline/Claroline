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
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CursusBundle\Entity\Registration\CourseUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseUserSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, CourseUser::class) => 'preCreate',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var CourseUser $courseUser */
        $courseUser = $event->getObject();

        $courseUser->setDate(new \DateTime());
    }
}
