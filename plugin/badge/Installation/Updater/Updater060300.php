<?php

namespace Icap\BadgeBundle\Installation\Updater;

use AppKernel;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater060300 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Connection $connection
     * @param AppKernel  $kernel
     */
    public function postInstall(Connection $connection, AppKernel $kernel)
    {
        $this->migrateBadgeTables($connection, $kernel);
    }

    /**
     * @param Connection $connection
     * @param AppKernel  $kernel
     */
    public function postUpdate(Connection $connection, AppKernel $kernel)
    {
        $this->migrateBadgeTables($connection, $kernel);
    }

    /**
     * @param Connection $connection
     * @param AppKernel  $kernel
     *
     * @throws \Claroline\MigrationBundle\Migrator\InvalidDirectionException
     * @throws \Claroline\MigrationBundle\Migrator\InvalidVersionException
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     */
    protected function migrateBadgeTables(Connection $connection, AppKernel $kernel)
    {
        $portfolioBundle = $this->container->get('claroline.persistence.object_manager')->getRepository('ClarolineCoreBundle:Plugin')->findBy(
            array('vendorName' => 'Icap', 'bundleName' => 'PortfolioBundle')
        );
        $portfolioBundle = count($portfolioBundle) === 1 ? true : false;

        if (!$portfolioBundle && $connection->getSchemaManager()->tablesExist(['icap__portfolio_widget_badges'])) {
            $this->log('Deleting portfolios badges tables...');
            $connection->getSchemaManager()->dropTable('icap__portfolio_widget_badges_badge');
            $connection->getSchemaManager()->dropTable('icap__portfolio_widget_badges');
            $this->log('Portfolios badges tables deleted.');
        }

        if ($portfolioBundle && !$connection->getSchemaManager()->tablesExist(['icap__portfolio_widget_badges'])) {
            $badgeBundle = $kernel->getBundle('IcapBadgeBundle');
            $this->log('Executing migrations for portfolio interaction');

            $migrationsDir = "{$badgeBundle->getPath()}/Installation/Migrations";
            $migrationsName = "{$badgeBundle->getName()} migration";
            $migrationsNamespace = "{$badgeBundle->getNamespace()}\\Installation\\Migrations";
            $migrationsTableName = 'doctrine_'.strtolower($badgeBundle->getName()).'_versions';

            $config = new Configuration($connection);
            $config->setName($migrationsName);
            $config->setMigrationsDirectory($migrationsDir);
            $config->setMigrationsNamespace($migrationsNamespace);
            $config->setMigrationsTableName($migrationsTableName);
            $config->registerMigrationsFromDirectory($migrationsDir);

            $migration = new Migration($config);
            $executedQueriesNumber = $migration->migrate('20150929141509');

            $this->log(sprintf('%d queries executed', $executedQueriesNumber));
        }
    }
}
