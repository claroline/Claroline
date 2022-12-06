<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SamlBundle\Security;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\UserChecker as BaseUserChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker extends BaseUserChecker
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ((!$this->config->getParameter('saml.reactivate_on_login') && !$user->isEnabled()) || $user->isRemoved()) {
            $message = $this->translator->trans('account_deleted', [
                '%support_email%' => $this->config->getParameter('help.support_email'),
            ], 'security');

            throw new AccessDeniedException($message);
        }
    }
}
