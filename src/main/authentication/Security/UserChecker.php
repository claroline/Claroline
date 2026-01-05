<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Security;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(
        protected readonly TranslatorInterface $translator,
        protected readonly PlatformConfigurationHandler $config
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isDisabled() || $user->isRemoved()) {
            $message = $this->translator->trans('account_deleted', [
                '%support_email%' => $this->config->getParameter('help.support_email') ?? '',
            ], 'security');

            throw new CustomUserMessageAccountStatusException($message);
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isAccountNonExpired()) {
            $message = $this->translator->trans('account_expired', [
                '%support_email%' => $this->config->getParameter('help.support_email') ?? '',
            ], 'security');

            throw new CustomUserMessageAccountStatusException($message);
        }
    }
}
