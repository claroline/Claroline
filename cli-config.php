<?php

/**
 * This file is required by bin/doctrine.php. It allows to use
 * doctrine commands missing in the default command set of
 * Symfony (like dbal:import).
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once 'app/bootstrap.php.cache';
require_once 'app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->boot();
$entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');

return ConsoleRunner::createHelperSet($entityManager);

