<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Installation\Updater;

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
        $this->log('Updates dropzone dates timezone...');

        $this->conn
            ->prepare('
                UPDATE claro_dropzonebundle_dropzone SET drop_start_date = DATE_SUB(drop_start_date, INTERVAL 2 HOUR) WHERE drop_start_date IS NOT NULL
            ')
            ->execute();

        $this->conn
            ->prepare('
                UPDATE claro_dropzonebundle_dropzone SET drop_end_date = DATE_SUB(drop_end_date, INTERVAL 2 HOUR) WHERE drop_end_date IS NOT NULL
            ')
            ->execute();

        $this->conn
            ->prepare('
                UPDATE claro_dropzonebundle_dropzone SET review_start_date = DATE_SUB(review_start_date, INTERVAL 2 HOUR) WHERE review_start_date IS NOT NULL
            ')
            ->execute();

        $this->conn
            ->prepare('
                UPDATE claro_dropzonebundle_dropzone SET review_end_date = DATE_SUB(review_end_date, INTERVAL 2 HOUR) WHERE review_end_date IS NOT NULL
            ')
            ->execute();
    }
}
