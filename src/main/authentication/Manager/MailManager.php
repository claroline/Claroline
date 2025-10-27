<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\ForgotPasswordEvent;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\MailManager as BaseMailManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Manages emails sent by the authentication system (e.g., password reset).
 */
class MailManager
{
    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly BaseMailManager $mailManager,
        private readonly LocaleManager $localeManager,
        private readonly TemplateManager $templateManager,
        private readonly UserManager $userManager
    ) {
    }

    public function sendForgotPassword(User $user): bool
    {
        $this->eventDispatcher->dispatch(new ForgotPasswordEvent($user), SecurityEvents::FORGOT_PASSWORD);

        $locale = $this->localeManager->getLocale($user);
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
        ];

        if ($user->isDisabled()) {
            $subject = $this->templateManager->getTemplate('user_disabled', $placeholders, $locale, 'title');
            $body = $this->templateManager->getTemplate('user_disabled', $placeholders, $locale);

            return $this->mailManager->send($subject, $body, [$user], null, [], true);
        }

        $this->userManager->initializePassword($user); // should not be done here (only manage email sending here)

        $placeholders['password_reset_link'] = $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL)."#/newpassword/{$user->getResetPasswordHash()}";

        $subject = $this->templateManager->getTemplate('forgotten_password', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('forgotten_password', $placeholders, $locale);

        return $this->mailManager->send($subject, $body, [$user], null, [], true);
    }

    public function sendInitPassword(User $user): bool
    {
        $this->userManager->initializePassword($user); // should not be done here (only manage email sending here)

        $locale = $this->localeManager->getLocale($user);
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password_initialization_link' => $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL)."#/newpassword/{$user->getResetPasswordHash()}",
        ];
        $subject = $this->templateManager->getTemplate('password_initialization', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('password_initialization', $placeholders, $locale);

        return $this->mailManager->send($subject, $body, [$user], null, [], true);
    }

    public function sendValidateEmail(User $user): bool
    {
        $locale = $this->localeManager->getLocale($user);
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'validation_mail' => $this->router->generate(
                'claro_security_validate_email',
                ['hash' => $user->getEmailValidationHash()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];

        $subject = $this->templateManager->getTemplate('user_email_validation', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('user_email_validation', $placeholders, $locale);

        return $this->mailManager->send($subject, $body, [$user], null, [], true);
    }
}
