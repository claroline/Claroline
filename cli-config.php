<?php

/**
 * This file is required by bin/doctrine.php. It allows to use
 * doctrine commands missing in the default command set of
 * Symfony (like dbal:import).
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use App\Kernel;

require_once 'app/bootstrap.php.cache';

$kernel = new Kernel('dev', true);
$kernel->boot();
$entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');

return ConsoleRunner::createHelperSet($entityManager);

