<?php

namespace Claroline\LogBundle\Subscriber;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\LogBundle\Entity\SecurityLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityLogSubscriber implements EventSubscriberInterface
{
    private $om;
    private $security;
    private $requestStack;
    private $translator;

    public function __construct(
        ObjectManager $om,
        Security $security,
        RequestStack $requestStack,
        TranslatorInterface $translator
    ) {
        $this->om = $om;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::USER_LOGIN => 'logEvent',
            SecurityEvents::USER_LOGOUT => 'logEvent',
            SecurityEvents::USER_DISABLE => 'logEvent',
            SecurityEvents::USER_ENABLE => 'logEvent',
            SecurityEvents::NEW_PASSWORD => 'logEvent',
            SecurityEvents::FORGOT_PASSWORD => 'logEvent',
            SecurityEvents::ADD_ROLE => 'logEvent',
            SecurityEvents::REMOVE_ROLE => 'logEvent',
            SecurityEvents::VIEW_AS => 'logEvent',
            SecurityEvents::VALIDATE_EMAIL => 'logEvent',
            SecurityEvents::AUTHENTICATION_FAILURE => 'logEvent',
            SecurityEvents::SWITCH_USER => 'logEventSwitchUser',
        ];
    }

    public function logEvent(Event $event, string $eventName): void
    {
        $logEntry = new SecurityLog();
        $logEntry->setDetails($event->getMessage($this->translator));
        $logEntry->setEvent($eventName);
        $logEntry->setTarget($event->getUser());
        $logEntry->setDoer($this->security->getUser() ?? $event->getUser());
        $logEntry->setDoerIp($this->getDoerIp());

        $this->om->persist($logEntry);
        $this->om->flush();
    }

    public function logEventSwitchUser(SwitchUserEvent $event, string $eventName): void
    {
        if (!$this->security->getToken() instanceof SwitchUserToken) {
            $logEntry = new SecurityLog();
            $logEntry->setDetails($this->translator->trans(
                'switchUser',
                [
                    'username' => $this->security->getUser(),
                    'target' => $event->getTargetUser(),
                ],
                'security'
            ));
            $logEntry->setEvent($eventName);
            $logEntry->setTarget($event->getTargetUser());
            $logEntry->setDoer($this->security->getUser());
            $logEntry->setDoerIp($this->getDoerIp());

            $this->om->persist($logEntry);
            $this->om->flush();
        }
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
