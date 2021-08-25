<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Manager;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AgendaBundle\Entity\EventInvitation;
use Claroline\AgendaBundle\Messenger\Message\SendEventInvitation;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\ICS\ICSGenerator;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\PlanningManager;
use Symfony\Component\Messenger\MessageBusInterface;

class EventManager
{
    /** @var ObjectManager */
    private $om;
    /** @var ICSGenerator */
    private $ics;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var PlanningManager */
    private $planningManager;

    public function __construct(
        ObjectManager $om,
        ICSGenerator $ics,
        MessageBusInterface $messageBus,
        PlanningManager $planningManager
    ) {
        $this->om = $om;
        $this->ics = $ics;
        $this->messageBus = $messageBus;
        $this->planningManager = $planningManager;
    }

    public function getICS(Event $event, bool $toFile = false): string
    {
        $location = $event->getLocation();
        $locationAddress = '';
        if ($location) {
            $locationAddress = $location->getName();
            $locationAddress .= '<br>'.$location->getAddress();
            if ($location->getPhone()) {
                $locationAddress .= '<br>'.$location->getPhone();
            }
        }

        $icsProps = [
            'summary' => $event->getName(),
            'description' => $event->getDescription(),
            'location' => $locationAddress,
            'dtstart' => DateNormalizer::normalize($event->getStartDate()),
            'dtend' => DateNormalizer::normalize($event->getEndDate()),
            'url' => null,
        ];

        if ($toFile) {
            return $this->ics->createFile($icsProps, $event->getUuid());
        }

        return $this->ics->create($icsProps);
    }

    public function createInvitation(Event $event, User $user)
    {
        $eventInvitation = $this->om->getRepository(EventInvitation::class)->findOneBy([
            'user' => $user,
            'event' => $event,
        ]);

        if (empty($eventInvitation)) {
            $eventInvitation = new EventInvitation($event, $user);

            $this->om->persist($eventInvitation);
            $this->om->flush();

            // add event to user planning
            $this->planningManager->addToPlanning($event, $user);

            $this->sendInvitation($event, [$user]);
        }

        return $eventInvitation;
    }

    public function removeInvitation(EventInvitation $invitation)
    {
        // remove event from user planning
        $this->planningManager->addToPlanning($invitation->getEvent(), $invitation->getUser());

        $this->om->remove($invitation);
        $this->om->flush();
    }

    public function sendInvitation(Event $event, array $users = [])
    {
        // create ics file to attach to the message
        $icsPath = $this->getICS($event, true);

        foreach ($users as $user) {
            /** @var EventInvitation $invitation */
            $invitation = $this->om->getRepository(EventInvitation::class)->findOneBy([
                'user' => $user,
                'event' => $event,
            ]);

            if ($invitation) {
                $this->messageBus->dispatch(new SendEventInvitation(
                    $invitation->getId(),
                    $icsPath
                ));
            }
        }
    }
}
