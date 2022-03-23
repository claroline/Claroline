<?php

namespace Claroline\LogBundle\Subscriber\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AuthenticationFailureEvent;
use Claroline\CoreBundle\Event\Security\ForgotPasswordEvent;
use Claroline\CoreBundle\Event\Security\NewPasswordEvent;
use Claroline\CoreBundle\Event\Security\UserLoginEvent;
use Claroline\CoreBundle\Event\Security\UserLogoutEvent;
use Claroline\CoreBundle\Event\Security\ViewAsEvent;
use Claroline\LogBundle\Messenger\Message\CreateSecurityLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationLogSubscriber implements EventSubscriberInterface
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
            SecurityEvents::USER_LOGIN => 'logUserLogin',
            SecurityEvents::USER_LOGOUT => 'logUserLogout',
            SecurityEvents::AUTHENTICATION_FAILURE => 'logAuthenticationFailure',
            SecurityEvents::VIEW_AS => 'logViewAs',
            SecurityEvents::SWITCH_USER => 'logSwitchUser',
            SecurityEvents::NEW_PASSWORD => 'logNewPassword',
            SecurityEvents::FORGOT_PASSWORD => 'logForgotPassword',
        ];
    }

    public function logUserLogin(UserLoginEvent $event, string $eventName): void
    {
        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $this->translator->trans('userLogin', [
                'username' => $event->getUser(),
            ], 'security'),
            $this->getDoerIp(),
            $event->getUser()->getId(),
            $event->getUser()->getId()
        ));
    }

    public function logUserLogout(UserLogoutEvent $event, string $eventName)
    {
        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $this->translator->trans('userLogout', [
                'username' => $event->getUser(),
            ], 'security'),
            $this->getDoerIp(),
            $event->getUser()->getId(),
            $event->getUser()->getId()
        ));
    }

    public function logAuthenticationFailure(AuthenticationFailureEvent $event, string $eventName): void
    {
        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $this->translator->trans('authenticationFailure', [
                'username' => $event->getUser(),
                'message' => $event->getMessage(),
            ], 'security'),
            $this->getDoerIp(),
            $event->getUser() ? $event->getUser()->getId() : null,
            $event->getUser() ? $event->getUser()->getId() : null
        ));
    }

    public function logViewAs(ViewAsEvent $event, string $eventName): void
    {
        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $this->translator->trans('viewAs', [
                'username' => $event->getUser(),
                'role' => $event->getRole(),
            ], 'security'),
            $this->getDoerIp(),
            $event->getUser()->getId(),
            $event->getUser()->getId()
        ));
    }

    public function logSwitchUser(SwitchUserEvent $event, string $eventName): void
    {
        if ($this->security->getToken() instanceof SwitchUserToken) {
            return;
        }

        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $this->translator->trans('switchUser', [
                'username' => $this->security->getUser(),
                'target' => $event->getTargetUser(),
            ], 'security'),
            $this->getDoerIp(),
            $this->security->getUser()->getId(),
            $event->getTargetUser()->getId()
        ));
    }

    public function logForgotPassword(ForgotPasswordEvent $event, string $eventName): void
    {
        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $this->translator->trans('forgotPassword', [
                'username' => $event->getUser(),
            ], 'security'),
            $this->getDoerIp(),
            $this->security->getUser() ? $this->security->getUser()->getId() : $event->getUser()->getId(),
            $event->getUser()->getId()
        ));
    }

    public function logNewPassword(NewPasswordEvent $event, string $eventName): void
    {
        $this->messageBus->dispatch(new CreateSecurityLog(
            new \DateTime(),
            $eventName,
            $this->translator->trans('newPassword', [
                'username' => $event->getUser(),
            ], 'security'),
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
