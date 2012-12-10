<?php

require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('install', true); // put second parameter to false when development is done
$kernel->loadClassCache();
$kernel->handle(Request::createFromGlobals())->send();