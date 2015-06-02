<?php

use Symfony\Component\Debug\Debug;

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
Debug::enable(E_ALL ^ ~E_DEPRECATED, false);

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.
/*
$apcLoader = new ApcClassLoader('sf2', $loader);
$loader->unregister();
$apcLoader->register(true);
*/

require_once __DIR__.'/../app/AppKernel.php';

$maintenanceMode = file_exists(__DIR__ . '/../app/config/.update');
$authorized = false;

if (file_exists($file = __DIR__ . '/../app/config/ips')) {
    $authorizedIps = file($file, FILE_IGNORE_NEW_LINES);
    $authorized = in_array($_SERVER['REMOTE_ADDR'], $authorizedIps);
}

if (!$maintenanceMode || $authorized) {
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();
    $kernel->handle($request)->send();
    //$kernel->terminate($request, $response);
} else {
    $url = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '/../maintenance.php';
    header("Location: http://{$url}");
}
