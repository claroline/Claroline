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

class Updater120505 extends Updater
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
        $this->removeSlugsBackup('claro_resource_node');
        $this->removeSlugsBackup('claro_workspace');
    }

    private function removeSlugsBackup($table)
    {
        $this->log("Removes backup slugs for table ${table}...");

        $charsToRemove = ['?', '#', '/', '(', ')'];
        for ($i = 0; $i < count($charsToRemove); ++$i) {
            // create a backup
            try {
                $this->conn->query("DROP TABLE IF EXISTS ${table}_slugs_${i}");
            } catch (\Exception $e) {
            }
        }
    }
}
