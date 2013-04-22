<?php
/**
* @TODO make a html css for the errors 
**/

require_once __DIR__ . '/../app/bootstrap.php.cache';
require_once __DIR__ . '/../app/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;

if (!file_exists($file = __DIR__ . '/../app/config/local/parameters.yml')) {
    touch($file);
}
if (!file_exists($file = __DIR__ . '/../app/config/local/plugin/routing.yml')) {
    touch($file);
}

// if (!is_writable(__DIR__.'../app/logs')) {
//     echo "<strong>Change the permission to write in the log folder</strong>";
// }
$kernel = new AppKernel('install', true); // put second parameter to false when development is done
$kernel->loadClassCache();
$kernel->handle(Request::createFromGlobals())->send();
