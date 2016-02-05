<?php

//This will execute the operations.xml file.
//You shouldn't execute it manually.
require_once __DIR__ . '/../../../../../../app/bootstrap.php.cache';
require_once __DIR__ . '/../../../../../../app/AppKernel.php';

use Claroline\CoreBundle\Library\Installation\Refresher;
use Symfony\Component\Console\Output\StreamOutput;

//The cache must be cleared first.
$cacheDir =  __DIR__ . '/../../../../../../app/cache';
Refresher::removeContentFrom($cacheDir);

$kernel = new AppKernel('dev', true); //'prod',  false pour l'env de prod
$kernel->loadClassCache();
$kernel->boot();
$container = $kernel->getContainer();
$bundleManager = $container->get('claroline.manager.bundle_manager');
$bundleManager->executeOperationFile($argv[1]);
