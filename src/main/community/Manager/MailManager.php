<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\MailManager as BaseMailManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailManager
{
    public function __construct(
        private UrlGeneratorInterface $router,
        private BaseMailManager $mailManager,
        private LocaleManager $localeManager,
        private TemplateManager $templateManager
    ) {
    }

    /**
     * Send a message when new user is created.
     */
    public function sendCreationMessage(User $user): bool
    {
        $locale = $this->localeManager->getLocale($user);
        $url = $this->router->generate(
            'claro_security_validate_email',
            ['hash' => $user->getEmailValidationHash()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'password' => $user->getPlainPassword(),
            'validation_mail' => $url,
        ];
        $subject = $this->templateManager->getTemplate('user_registration', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('user_registration', $placeholders, $locale);

        return $this->mailManager->send($subject, $body, [$user], null, [], true);
    }

    /**
     * Send a message when new user is created, and it needs to validate its registration.
     */
    public function sendEnableAccountMessage(User $user): bool
    {
        $hash = $user->getResetPasswordHash();
        $link = $this->router->generate(
            'claro_security_activate_user',
            ['hash' => $hash],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $locale = $this->localeManager->getLocale($user);
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'user_activation_link' => $link,
        ];
        $subject = $this->templateManager->getTemplate('user_activation', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('user_activation', $placeholders, $locale);

        return $this->mailManager->send($subject, $body, [$user], null, [], true);
    }
}
