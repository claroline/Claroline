<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Version;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.migration_manager")
 */
class MigrationManager
{
    /**
     * @DI\InjectParams({
     *      "connection" = @DI\Inject("database_connection")
     * })
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function exists($bundle, $version)
    {
        $version = $this->buildVersion($bundle, $version);

        return $version->isMigrated();
    }

    public function mark($bundle, $version)
    {
        $version = $this->buildVersion($bundle, $version);
        $version->markMigrated();

        return $this;
    }

    private function buildVersion($bundle, $version)
    {
        $bundle = strtolower($bundle);
        $config = new Configuration($this->connection);
        $config->setMigrationsTableName('doctrine_'.$bundle.'_versions');
        $config->setMigrationsNamespace(ucfirst($bundle)); // required but useless
        $config->setMigrationsDirectory(ucfirst($bundle)); // idem

        return new Version($config, $version, 'stdClass');
    }
}
