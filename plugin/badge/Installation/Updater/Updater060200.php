<?php

namespace Icap\BadgeBundle\Installation\Updater;

use AppKernel;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Version;

class Updater060200 extends Updater
{
    /**
     * @param Connection $connection
     * @param AppKernel  $kernel
     */
    public function preUpdate(Connection $connection, AppKernel $kernel)
    {
        /** @var \Symfony\Component\HttpKernel\Bundle\Bundle[] $bundles */
        $bundles = $kernel->getBundles();
        $isPortfolioBundleInstalled = false;
        foreach ($bundles as $bundle) {
            if ('IcapPortfolioBundle' === $bundle->getName()) {
                $isPortfolioBundleInstalled = true;
            }
        }

        if ($connection->getSchemaManager()->tablesExist(['icap__portfolio_widget_badges'])) {
            if ($isPortfolioBundleInstalled) {
                $this->log('Found existing database schema: skipping install migration...');
                $config = new Configuration($connection);
                $config->setMigrationsTableName('doctrine_icapbadgebundle_versions');
                $config->setMigrationsNamespace('claro_badge'); // required but useless
                $config->setMigrationsDirectory('claro_badge'); // idem
                try {
                    $version = new Version($config, '20150929141509', 'stdClass');
                    $version->markMigrated();
                } catch (\Exception $e) {
                    $this->log('Already migrated');
                }
            } else {
                $this->log('Deleting badges tables for portfolio...');
                $connection->getSchemaManager()->dropTable('icap__portfolio_widget_badges_badge');
                $connection->getSchemaManager()->dropTable('icap__portfolio_widget_badges');
                $this->log('badges tables for portfolio deleted.');
            }
        }
    }
}
