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

use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Subscriber\Crud\Planning\AbstractPlannedSubscriber;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\EventManager;
use Ramsey\Uuid\Uuid as BaseUuid;

class EventSubscriber extends AbstractPlannedSubscriber
{
    public function __construct(
        private readonly EventManager $manager
    ) {
    }

    public static function getPlannedClass(): string
    {
        return Event::class;
    }

    public function preCreate(CreateEvent $event): void
    {
        parent::preCreate($event);

        /** @var Event $object */
        $object = $event->getObject();

        // add event to session and workspace planning
        if ($object->getSession()) {
            $this->planningManager->addToPlanning($object, $object->getSession());

            if ($object->getSession()->getWorkspace()) {
                $this->planningManager->addToPlanning($object, $object->getSession()->getWorkspace());
            }
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        parent::postCreate($event);

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

    public function preCopy(CopyEvent $event): void
    {
        parent::preCopy($event);

        /** @var Session $session */
        $session = $event->getExtra()['parent'];

        /** @var Event $original */
        $original = $event->getObject();

        /** @var Event $copy */
        $copy = $event->getCopy();

        $copy->setUuid(BaseUuid::uuid4()->toString());

        $copyCode = $this->om->getRepository(Event::class)->findNextUnique('code', $original->getCode());

        $copy->setCode($copyCode);
        $copy->setSession($session);
    }
}
