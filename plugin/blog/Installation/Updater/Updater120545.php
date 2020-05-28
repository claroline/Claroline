<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\BlogBundle\Library\Installation\Updater;

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
        $this->log('Updates blog posts dates timezone...');

        $this->conn
            ->prepare('
                UPDATE icap__blog_post SET publication_date = DATE_SUB(publication_date, INTERVAL 2 HOUR) WHERE publication_date IS NOT NULL
            ')
            ->execute();
    }
}
