<?php

namespace Claroline\CoreBundle\Library\Security;

class PlatformRoles
{
    const USER = 'ROLE_USER';
    const WS_CREATOR = 'ROLE_WS_CREATOR';
    const ADMIN = 'ROLE_ADMIN';
    
    private static $roles = array(
        self::USER,
        self::WS_CREATOR,
        self::ADMIN
    );
    
    public static function contains($roleName)
    {
        return in_array($roleName, self::$roles);
    }
}