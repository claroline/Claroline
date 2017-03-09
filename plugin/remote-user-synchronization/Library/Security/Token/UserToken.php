<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RemoteUserSynchronizationBundle\Library\Security\Token;

use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class UserToken extends AbstractToken
{
    public function __construct(User $user)
    {
        $roles = $user->getRoles();
        parent::__construct($roles);

        // If the user has roles, consider it authenticated
        $this->setAuthenticated(count($roles) > 0);
        $this->setUser($user);
    }

    public function getCredentials()
    {
        return '';
    }
}
