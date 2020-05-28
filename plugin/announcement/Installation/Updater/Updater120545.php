<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Installation\Updater;

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
        $this->log('Updates announcement dates timezone...');

        $this->conn
            ->prepare('
                UPDATE claro_announcement SET visible_from = DATE_SUB(visible_from, INTERVAL 2 HOUR) WHERE visible_from IS NOT NULL
            ')
            ->execute();

        $this->conn
            ->prepare('
                UPDATE claro_announcement SET visible_until = DATE_SUB(visible_until, INTERVAL 2 HOUR) WHERE visible_until IS NOT NULL
            ')
            ->execute();
    }
}
