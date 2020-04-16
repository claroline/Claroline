<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @deprecated use Claroline\AppBundle\Manager\SecurityManager
 */
class Utilities
{
    /**
     * Returns the roles (an array of string) of the $token.
     *
     * @param TokenInterface $token
     *
     * @return array
     */
    public function getRoles(TokenInterface $token)
    {
        $roles = [];

        foreach ($token->getRoles() as $role) {
            $roles[] = $role->getRole();
        }

        return $roles;
    }
}
