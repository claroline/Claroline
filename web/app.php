<?php

require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';

$maintenanceMode = file_exists(__DIR__ . '/../.update');

if (!$maintenanceMode) {
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();
    $kernel->handle($request)->send();
} else {
    header('Location:maintenance.html');
}
