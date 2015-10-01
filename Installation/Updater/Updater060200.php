<?php

namespace Icap\BadgeBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Version;
use Doctrine\DBAL\Connection;

class Updater060200 extends Updater
{
    /**
     * @param Connection $connection
     */
    public function preUpdate(Connection $connection)
    {
        if ($connection->getSchemaManager()->tablesExist(['icap__portfolio_widget_badges'])) {
            $this->log('Found existing database schema: skipping install migration...');
            $config = new Configuration($connection);
            $config->setMigrationsTableName('doctrine_icapbadgebundle_versions');
            $config->setMigrationsNamespace('claro_badge'); // required but useless
            $config->setMigrationsDirectory('claro_badge'); // idem
            $version = new Version($config, '20150929141509', 'stdClass');
            $version->markMigrated();
        }
    }
}
