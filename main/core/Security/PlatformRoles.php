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

class PlatformRoles
{
    const USER = 'ROLE_USER';
    const WS_CREATOR = 'ROLE_WS_CREATOR';
    const ADMIN = 'ROLE_ADMIN';
    const ANONYMOUS = 'ROLE_ANONYMOUS';

    private static $roles = array(
        self::USER,
        self::WS_CREATOR,
        self::ADMIN,
        self::ANONYMOUS,
    );

    public static function contains($roleName)
    {
        return in_array($roleName, self::$roles);
    }
}
