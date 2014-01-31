<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MaintenanceHandler {

    public static function enableMaintenance()
    {
        touch(__DIR__ . '/.update');
    }

    public static function disableMaintenance()
    {
        unlink(__DIR__ . '/.update');
    }

} 