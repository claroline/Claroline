<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Maintenance;

class MaintenanceHandler 
{

    const PATH = '/../../../../../../../app/config';

    public static function enableMaintenance()
    {
        touch(__DIR__ . self::PATH . '/.update');
    }

    public static function disableMaintenance()
    {
        unlink(__DIR__ . self::PATH . '/.update');
    }

} 
