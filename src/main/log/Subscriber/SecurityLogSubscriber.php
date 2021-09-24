<?php

namespace Claroline\LogBundle\Subscriber;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AbstractRoleEvent;
use Claroline\CoreBundle\Library\GeoIp\GeoIpInfoProviderInterface;
use Claroline\LogBundle\Messenger\Message\CreateRoleChangeLogs;
use Claroline\LogBundle\Messenger\Message\CreateSecurityLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityLogSubscriber implements EventSubscriberInterface
{
    /** @var Security */
    private $security;
    /** @var RequestStack */
    private $requestStack;
    /** @var TranslatorInterface */
    private $translator;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var GeoIpInfoProviderInterface|null */
    private $geoIpInfoProvider;

    public function __construct(
        Security $security,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        MessageBusInterface $messageBus,
        ?GeoIpInfoProviderInterface $geoIpInfoProvider = null
    ) {
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->messageBus = $messageBus;
        $this->geoIpInfoProvider = $geoIpInfoProvider;
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
            SecurityEvents::VIEW_AS => 'logEvent',
            SecurityEvents::VALIDATE_EMAIL => 'logEvent',
            SecurityEvents::AUTHENTICATION_FAILURE => 'logEvent',
            SecurityEvents::ADD_ROLE => 'logRoleChanges',
            SecurityEvents::REMOVE_ROLE => 'logRoleChanges',
            SecurityEvents::SWITCH_USER => 'logEventSwitchUser',
        ];
    }

    public function logEvent(Event $event, string $eventName): void
    {
        $doerInfo = $this->getDoerInfo();

        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $event->getMessage($this->translator), // this should not be done by the symfony event
            $doerInfo['ip'],
            $this->security->getUser() ?? $event->getUser(),
            $event->getUser(),
            $doerInfo['city'],
            $doerInfo['country']
        ));
    }

    public function logRoleChanges(AbstractRoleEvent $event, string $eventName)
    {
        $doerInfo = $this->getDoerInfo();

        $this->messageBus->dispatch(new CreateRoleChangeLogs(
            new \DateTime(),
            $eventName,
            $event->getRole(),
            $doerInfo['ip'],
            $this->security->getUser(),
            $event->getUsers(),
            $doerInfo['city'],
            $doerInfo['country']
        ));
    }

    public function logEventSwitchUser(SwitchUserEvent $event, string $eventName): void
    {
        if ($this->security->getToken() instanceof SwitchUserToken) {
            return;
        }

        $doerInfo = $this->getDoerInfo();

        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $this->translator->trans(
                'switchUser',
                [
                    'username' => $this->security->getUser(),
                    'target' => $event->getTargetUser(),
                ],
                'security'
            ), // this should not be done by the symfony event
            $doerInfo['ip'],
            $this->security->getUser(),
            $event->getTargetUser(),
            $doerInfo['city'],
            $doerInfo['country']
        ));
    }

    private function getDoerInfo(): array
    {
        $doerIp = $this->getDoerIp();

        $doerCountry = null;
        $doerCity = null;
        if ($this->geoIpInfoProvider && 'CLI' !== $doerIp) {
            $geoIpInfo = $this->geoIpInfoProvider->getGeoIpInfo($doerIp);

            if ($geoIpInfo) {
                $doerCountry = $geoIpInfo->getCountry();
                $doerCity = $geoIpInfo->getCity();
            }
        }

        return [
            'ip' => $doerIp,
            'city' => $doerCity,
            'country' => $doerCountry,
        ];
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
