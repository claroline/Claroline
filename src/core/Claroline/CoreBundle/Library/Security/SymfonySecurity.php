<?php

namespace Claroline\CoreBundle\Library\Security;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class SymfonySecurity
{

    public static function getSfCodes()
    {
        return array(
            1 => 'V',
            2 => 'C',
            4 => 'E',
            8 => 'D',
            16 => 'U',
            32 => 'O',
            64 => 'M',
            128 => 'N'
        );
    }

    public static function getSfMasks()
    {
        return array(
            1 => 'VIEW',
            2 => 'CREATE',
            4 => 'EDIT',
            8 => 'DELETE',
            16 => 'UNDELETE',
            32 => 'OPERATOR',
            64 => 'MASTER',
            128 => 'OWNER',
        );
    }

    public static function getResourcesMasks()
    {
        return array(
            1 => 'VIEW',
            2 => 'CREATE',
            4 => 'EDIT',
            8 => 'DELETE',
            128 => 'OWNER',
        );
    }

    /**
     * Returns a mask as a permission array
     *
     * @param integer $mask
     *
     * @return array
     */
    public static function getArrayPermissions($mask)
    {
        $permissions = array();
        $masks = self::getSfMasks();
        $keys = array_keys($masks);

        foreach ($keys as $key) {
            if ($mask & $key) {
                $permissions[$mask] = $masks[$mask];
            }
        }

        return $permissions;
    }
}