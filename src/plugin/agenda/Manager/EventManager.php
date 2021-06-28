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
use Claroline\CoreBundle\Manager\PlanningManager;
use Symfony\Component\Messenger\MessageBusInterface;

class EventManager
{
    /** @var ObjectManager */
    private $om;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var PlanningManager */
    private $planningManager;

    public function __construct(
        ObjectManager $om,
        MessageBusInterface $messageBus,
        PlanningManager $planningManager
    ) {
        $this->om = $om;
        $this->messageBus = $messageBus;
        $this->planningManager = $planningManager;
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
        foreach ($users as $user) {
            $invitation = $this->om->getRepository('ClarolineAgendaBundle:EventInvitation')->findOneBy([
                'user' => $user,
                'event' => $event,
            ]);

            $this->messageBus->dispatch(new SendEventInvitation(
                $invitation->getId()
            ));
        }
    }
}
