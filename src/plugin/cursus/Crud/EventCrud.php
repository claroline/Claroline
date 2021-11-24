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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\EventManager;

class EventCrud
{
    /** @var ObjectManager */
    private $om;
    /** @var EventManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        EventManager $manager
    ) {
        $this->om = $om;
        $this->manager = $manager;
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var Event $trainingEvent */
        $trainingEvent = $event->getObject();

        if (Session::REGISTRATION_AUTO === $trainingEvent->getRegistrationType() && !$trainingEvent->isTerminated()) {
            // register session users to the new event

            /** @var SessionUser[] $sessionLearners */
            $sessionLearners = $this->om->getRepository(SessionUser::class)->findBy([
                'session' => $trainingEvent->getSession(),
                'type' => AbstractRegistration::LEARNER,
                'confirmed' => true,
                'validated' => true,
            ]);

            if (!empty($sessionLearners)) {
                $this->manager->addUsers($trainingEvent, array_map(function (SessionUser $sessionUser) {
                    return $sessionUser->getUser();
                }, $sessionLearners), AbstractRegistration::LEARNER);
            }

            $sessionTutors = $this->om->getRepository(SessionUser::class)->findBy([
                'session' => $trainingEvent->getSession(),
                'type' => AbstractRegistration::TUTOR,
                'confirmed' => true,
                'validated' => true,
            ]);

            if (!empty($sessionTutors)) {
                $this->manager->addUsers($trainingEvent, array_map(function (SessionUser $sessionUser) {
                    return $sessionUser->getUser();
                }, $sessionTutors), AbstractRegistration::TUTOR);
            }

            /** @var SessionGroup[] $sessionGroups */
            $sessionGroups = $this->om->getRepository(SessionGroup::class)->findBy([
                'session' => $trainingEvent->getSession(),
            ]);

            if (!empty($sessionGroups)) {
                $this->manager->addGroups($trainingEvent, array_map(function (SessionGroup $sessionGroup) {
                    return $sessionGroup->getGroup();
                }, $sessionGroups));
            }
        }
    }
}
