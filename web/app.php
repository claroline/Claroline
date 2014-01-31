<?php

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

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

if (!$maintenanceMode) {
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();
    $kernel->handle($request)->send();
    $kernel->terminate($request, $response);
} else {
    header('Location:maintenance.html');
}