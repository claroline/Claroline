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
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Security\UserChecker as BaseUserChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker extends BaseUserChecker
{
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(PlatformConfigurationHandler $config)
    {
        $this->config = $config;
    }

    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if (($this->config->getParameter('saml.reactivate_on_login') && !$user->isEnabled()) || $user->isRemoved()) {
            throw new AccessDeniedException('Your user account no longer exists.');
        }
    }
}
