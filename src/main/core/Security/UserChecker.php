<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChecker implements UserCheckerInterface
{
    /** @var TranslatorInterface */
    protected $translator;
    /** @var PlatformConfigurationHandler */
    protected $config;

    public function __construct(
        TranslatorInterface $translator,
        PlatformConfigurationHandler $config
    ) {
        $this->translator = $translator;
        $this->config = $config;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isEnabled() || $user->isRemoved()) {
            $message = $this->translator->trans('account_deleted', [
                '%support_email%' => $this->config->getParameter('help.support_email'),
            ], 'security');

            throw new AccessDeniedException($message);
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isAccountNonExpired()) {
            $message = $this->translator->trans('account_expired', [
                '%support_email%' => $this->config->getParameter('help.support_email'),
            ], 'security');

            throw new AccessDeniedException($message);
        }
    }
}
