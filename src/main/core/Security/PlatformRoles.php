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

final class PlatformRoles
{
    public const USER = 'ROLE_USER';
    public const WS_CREATOR = 'ROLE_WS_CREATOR';
    public const ADMIN = 'ROLE_ADMIN';
    public const ANONYMOUS = 'ROLE_ANONYMOUS';

    private static array $roles = [
        self::USER,
        self::WS_CREATOR,
        self::ADMIN,
        self::ANONYMOUS,
    ];

    public static function contains(string $roleName): bool
    {
        return in_array($roleName, self::$roles);
    }
}
