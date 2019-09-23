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

class Updater120504 extends Updater
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
        $this->fixSlugs('claro_resource_node');
        $this->fixSlugs('claro_workspace');
    }

    private function fixSlugs($table)
    {
        $this->log("Fixing slugs for table ${table}...");

        $charsToRemove = ['?', '#', '/', '(', ')'];
        foreach ($charsToRemove as $index => $char) {
            // create a backup
            try {
                $this->conn->query("CREATE TABLE ${table}_slugs_${index} AS (
                    SELECT * FROM ${table} WHERE slug LIKE '%${char}%'
                )");
            } catch (\Exception $e) {
                $this->log('No need backup');
            }

            $sql = "
                UPDATE claro_resource_node node SET slug = REGEXP_REPLACE(SUBSTR(CONCAT(node.name, '-', node.id),1,100), '[^A-Za-z0-9]+', '-') WHERE slug LIKE '%${$char}%'
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }
    }
}
