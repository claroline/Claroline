<?php

include __DIR__ . '/authorize.php';
$vendorDir = __DIR__ . '/../../vendor';
require $vendorDir . '/autoload.php';

use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;

$_GET['on'] === '1' ?
    MaintenanceHandler::enableMaintenance():
    MaintenanceHandler::disableMaitnenance();
    
return 0;
