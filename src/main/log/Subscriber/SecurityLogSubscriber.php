<?php

namespace Claroline\LogBundle\Subscriber;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Library\GeoIp\GeoIpInfoProviderInterface;
use Claroline\LogBundle\Messenger\Message\CreateSecurityLog;
use Claroline\LogBundle\Messenger\Message\CreateSecurityLogs;
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
            SecurityEvents::ADD_ROLE => 'logRoleChanges',
            SecurityEvents::REMOVE_ROLE => 'logRoleChanges',
            SecurityEvents::VIEW_AS => 'logEvent',
            SecurityEvents::VALIDATE_EMAIL => 'logEvent',
            SecurityEvents::AUTHENTICATION_FAILURE => 'logEvent',
            SecurityEvents::SWITCH_USER => 'logEventSwitchUser',
        ];
    }

    public function logEvent(Event $event, string $eventName): void
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

        $this->messageBus->dispatch(new CreateSecurityLog(
            $eventName,
            $event->getMessage($this->translator), // this should not be done by the symfony event
            $doerIp,
            $this->security->getUser() ?? $event->getUser(),
            $event->getUser(),
            $doerCity,
            $doerCountry
        ));
    }

    public function logRoleChanges(Event $event, string $eventName)
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

        $this->messageBus->dispatch(new CreateSecurityLogs(
            $eventName,
            'test messenger', // this should not be done by the symfony event
            $doerIp,
            $this->security->getUser(),
            $event->getUsers(),
            $doerCity,
            $doerCountry
        ));
    }

    public function logEventSwitchUser(SwitchUserEvent $event, string $eventName): void
    {
        if ($this->security->getToken() instanceof SwitchUserToken) {
            return;
        }

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

        $this->messageBus->dispatch(new CreateSecurityLog(
            $eventName,
            $this->translator->trans(
                'switchUser',
                [
                    'username' => $this->security->getUser(),
                    'target' => $event->getTargetUser(),
                ],
                'security'
            ), // this should not be done by the symfony event
            $doerIp,
            $this->security->getUser(),
            $event->getTargetUser(),
            $doerCity,
            $doerCountry
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
