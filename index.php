<?php

require_once __DIR__ . '/../../autoload.php';

use Claroline\WebInstaller\Kernel;
use Symfony\Component\HttpFoundation\Request;

$kernel = new Kernel(__DIR__);
$kernel->handle(Request::createFromGlobals());
