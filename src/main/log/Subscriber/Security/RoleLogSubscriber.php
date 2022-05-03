<?php

namespace Claroline\LogBundle\Subscriber\Security;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AbstractRoleEvent;
use Claroline\LogBundle\Messenger\Message\CreateRoleChangeLogs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class RoleLogSubscriber implements EventSubscriberInterface
{
    /** @var Security */
    private $security;
    /** @var RequestStack */
    private $requestStack;
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(
        Security $security,
        RequestStack $requestStack,
        MessageBusInterface $messageBus
    ) {
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::ADD_ROLE => 'logRoleChanges',
            SecurityEvents::REMOVE_ROLE => 'logRoleChanges',
        ];
    }

    public function logRoleChanges(AbstractRoleEvent $event, string $eventName)
    {
        $this->messageBus->dispatch(new CreateRoleChangeLogs(
            new \DateTime(),
            $eventName,
            $event->getRole()->getId(),
            $this->getDoerIp(),
            $this->security->getUser() ? $this->security->getUser()->getId() : null,
            array_map(function (User $user) {
                return $user->getId();
            }, $event->getUsers())
        ));
    }

    private function getDoerIp(): string
    {
        $doerIp = 'CLI';

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $doerIp = $request->getClientIp();
        }

        return $doerIp;
    }
}
