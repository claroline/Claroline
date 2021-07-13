<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Subscriber;

use Claroline\LogBundle\Entity\FunctionalLog;
use Claroline\OpenBadgeBundle\Event\BadgeEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class BadgeLogSubscriber implements EventSubscriberInterface
{
    private $translator;
    private $em;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BadgeEvents::ADD_BADGE => 'logEvent',
            BadgeEvents::REMOVE_BADGE => 'logEvent',
        ];
    }

    public function logEvent(Event $event, string $eventName)
    {
        $logEntry = new FunctionalLog();

        $logEntry->setUser($event->getUser());
        $logEntry->setDetails($event->getMessage($this->translator));
        $logEntry->setEvent($eventName);

        $this->em->persist($logEntry);
        $this->em->flush();
    }
}
