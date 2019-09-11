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
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120502 extends Updater
{
    /** @var ContainerInterface */
    private $container;
    private $conn;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->fixesDirectoriesPageSizes();
    }

    private function fixesDirectoriesPageSizes()
    {
        // 50 and 100 are old values from incorrect migration.
        // it blocks the directory validation
        $stmt = $this->conn->prepare('
            UPDATE claro_directory 
            SET availablePageSizes = REPLACE(REPLACE(availablePageSizes, "100", "120"), "50", "60") 
            WHERE availablePageSizes LIKE "%50%" OR availablePageSizes LIKE "%100%"
        ');
        $stmt->execute();
    }
}
