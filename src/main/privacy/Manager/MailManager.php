<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PrivacyBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\MailManager as BaseMailManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;

class MailManager
{
    public function __construct(
        private readonly BaseMailManager $mailManager,
        private readonly LocaleManager $localeManager,
        private readonly TemplateManager $templateManager,
        private readonly PrivacyManager $privacyManager,
    ) {
    }

    public function sendRequestToDPO(User $user): bool
    {
        $privacy = $this->privacyManager->getParameters();
        if (empty($privacy->getDpoEmail())) {
            return false;
        }

        $locale = $this->localeManager->getLocale($user);

        $placeholders = [
            'id' => $user->getUuid(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'password' => $user->getPlainPassword(),
            'email' => $user->getEmail(),
        ];

        $compiledTemplate = $this->templateManager->compile('request_account_deletion', $placeholders, $locale);

        return $this->mailManager->send($compiledTemplate->getTitle(), $compiledTemplate->getContent(), [], null, ['to' => [$privacy->getDpoEmail()]]);
    }
}
