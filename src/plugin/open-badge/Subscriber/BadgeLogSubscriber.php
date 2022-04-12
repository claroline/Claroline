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

use Claroline\LogBundle\Messenger\Message\CreateFunctionalLog;
use Claroline\OpenBadgeBundle\Event\AddBadgeEvent;
use Claroline\OpenBadgeBundle\Event\BadgeEvents;
use Claroline\OpenBadgeBundle\Event\RemoveBadgeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BadgeLogSubscriber implements EventSubscriberInterface
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(
        TranslatorInterface $translator,
        MessageBusInterface $messageBus
    ) {
        $this->translator = $translator;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BadgeEvents::ADD_BADGE => 'logAddBadge',
            BadgeEvents::REMOVE_BADGE => 'logRemoveBadge',
        ];
    }

    public function logAddBadge(AddBadgeEvent $event)
    {
        $user = $event->getUser();
        $badge = $event->getBadge();

        $this->messageBus->dispatch(new CreateFunctionalLog(
            new \DateTime(),
            BadgeEvents::ADD_BADGE,
            $this->translator->trans('addBadge', ['userName' => $user->getUsername(), 'badgeName' => $badge->getName()], 'functional'),
            $user->getId(),
            $badge->getWorkspace() ? $badge->getWorkspace()->getId() : null
        ));
    }

    public function logRemoveBadge(RemoveBadgeEvent $event)
    {
        $user = $event->getUser();
        $badge = $event->getBadge();

        $this->messageBus->dispatch(new CreateFunctionalLog(
            new \DateTime(),
            BadgeEvents::REMOVE_BADGE,
            $this->translator->trans('removeBadge', ['userName' => $user->getUsername(), 'badgeName' => $badge->getName()], 'functional'),
            $user->getId(),
            $badge->getWorkspace() ? $badge->getWorkspace()->getId() : null
        ));
    }
}
