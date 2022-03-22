<?php

namespace Claroline\LogBundle\Subscriber\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\UserDisableEvent;
use Claroline\CoreBundle\Event\Security\UserEnableEvent;
use Claroline\CoreBundle\Event\Security\ValidateEmailEvent;
use Claroline\LogBundle\Messenger\Message\CreateSecurityLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserLogSubscriber implements EventSubscriberInterface
{
    /** @var Security */
    private $security;
    /** @var RequestStack */
    private $requestStack;
    /** @var TranslatorInterface */
    private $translator;
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(
        Security $security,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        MessageBusInterface $messageBus
    ) {
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::USER_ENABLE => 'logUserEnable',
            SecurityEvents::USER_DISABLE => 'logUserDisable',
            SecurityEvents::VALIDATE_EMAIL => 'logValidateEmail',
        ];
    }

    public function logUserEnable(UserEnableEvent $event, string $eventName): void
    {
        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $this->translator->trans('userEnable', [
                'username' => $event->getUser(),
            ], 'security'),
            $this->getDoerIp(),
            $this->security->getUser() ? $this->security->getUser()->getId() : $event->getUser()->getId(),
            $event->getUser()->getId()
        ));
    }

    public function logUserDisable(UserDisableEvent $event, string $eventName): void
    {
        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $this->translator->trans('userDisable', [
                'username' => $event->getUser(),
            ], 'security'),
            $this->getDoerIp(),
            $this->security->getUser() ? $this->security->getUser()->getId() : $event->getUser()->getId(),
            $event->getUser()->getId()
        ));
    }

    public function logValidateEmail(ValidateEmailEvent $event, string $eventName): void
    {
        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $event->getMessage($this->translator),
            $this->getDoerIp(),
            $this->security->getUser() ? $this->security->getUser()->getId() : $event->getUser()->getId(),
            $event->getUser()->getId()
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
