<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

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
        $tables = ['claro_resource_node', 'claro_workspace'];
        foreach ($tables as $table) {
            $this->log(sprintf('Updates %s restrictions timezone...', $table));

            $this->conn
                ->prepare("
                    UPDATE {$table} SET accessible_from = DATE_SUB(accessible_from, INTERVAL 2 HOUR) WHERE accessible_from IS NOT NULL
                ")
                ->execute();

            $this->conn
                ->prepare("
                    UPDATE {$table} SET accessible_until = DATE_SUB(accessible_until, INTERVAL 2 HOUR) WHERE accessible_until IS NOT NULL
                ")
                ->execute();
        }

        $this->log('Updates scheduled tasks dates timezone...');

        $this->conn
            ->prepare('
                UPDATE claro_scheduled_task SET scheduled_date = DATE_SUB(scheduled_date, INTERVAL 2 HOUR) WHERE scheduled_date IS NOT NULL
            ')
            ->execute();
    }
}
