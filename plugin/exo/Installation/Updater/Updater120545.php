<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120545 extends Updater
{
    /** @var Connection */
    private $conn;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->fixTimezone();
    }

    private function fixTimezone()
    {
        $this->log('Updates exo dates timezone...');

        $this->conn
            ->prepare('
                UPDATE claro_event SET start_date = DATE_SUB(start_date, INTERVAL 2 HOUR) WHERE start_date IS NOT NULL
            ')
            ->execute();

        $this->conn
            ->prepare('
                UPDATE claro_event SET end_date = DATE_SUB(end_date, INTERVAL 2 HOUR) WHERE end_date IS NOT NULL
            ')
            ->execute();
    }
}
