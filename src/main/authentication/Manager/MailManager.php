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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\ForgotPasswordEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\MailManager as BaseMailManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Manages emails sent by the authentication system (eg. password reset).
 */
class MailManager
{
    public function __construct(
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $router,
        private StrictDispatcher $dispatcher,
        private PlatformConfigurationHandler $config,
        private BaseMailManager $mailManager,
        private LocaleManager $localeManager,
        private TemplateManager $templateManager,
        private UserManager $userManager
    ) {
    }

    public function sendForgotPassword(User $user): bool
    {
        $this->dispatcher->dispatch(SecurityEvents::FORGOT_PASSWORD, ForgotPasswordEvent::class, [$user]);

        $locale = $this->localeManager->getLocale($user);
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
        ];

        if (!$user->isEnabled()) {
            $subject = $this->templateManager->getTemplate('user_disabled', $placeholders, $locale, 'title');
            $body = $this->templateManager->getTemplate('user_disabled', $placeholders, $locale);

            return $this->mailManager->send($subject, $body, [$user], null, [], true);
        }

        $this->userManager->initializePassword($user); // should not be done here (only manage email sending here)

        $placeholders['password_reset_link'] = $this->router->generate(
            'claro_index',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        )."#/newpassword/{$user->getResetPasswordHash()}";

        $subject = $this->templateManager->getTemplate('forgotten_password', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('forgotten_password', $placeholders, $locale);

        return $this->mailManager->send($subject, $body, [$user], null, [], true);
    }

    public function sendInitPassword(User $user): bool
    {
        $this->userManager->initializePassword($user); // should not be done here (only manage email sending here)

        $link = $this->router->generate(
            'claro_index',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        )."#/newpassword/{$user->getResetPasswordHash()}";
        $locale = $this->localeManager->getLocale($user);
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'password_initialization_link' => $link,
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

    // TODO : move in Privacy plugin when available
    public function sendRequestToDPO(User $user): bool
    {
        $name = $user->getFullName();
        $idUser = $user->getId();
        $dpoEmail = $this->config->getParameter('privacy.dpo.email');
        $locale = $user->getLocale();

        $subject = $this->translator->trans('account_deletion.subject', [], 'privacy', $locale);
        $content = $this->translator->trans('account_deletion.body', ['%name%' => $name, '%id%' => $idUser], 'privacy', $locale);
        $body = $this->templateManager->getTemplate('email_layout', ['content' => $content], $locale);

        return $this->mailManager->send($subject, $body, [], null, ['to' => [$dpoEmail]]);
    }
}
