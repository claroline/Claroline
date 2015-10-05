<?php

namespace Icap\PortfolioBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Version;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Widget\WidgetType;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

class Updater060200 extends Updater
{
    public function postUpdate(Connection $connection, \AppKernel $kernel)
    {
        // Delete portfolio badges tables if the BadgeBundle is not installed
        /** @var \Symfony\Component\HttpKernel\Bundle\Bundle[] $bundles */
        $bundles = $kernel->getBundles();
        $isBadgeBundleInstalled = false;
        foreach ($bundles as $bundle) {
            if ('IcapBadgeBundle' === $bundle->getName()) {
                $isBadgeBundleInstalled = true;
            }
        }

        if (!$isBadgeBundleInstalled && $connection->getSchemaManager()->tablesExist(['icap__portfolio_widget_badges'])) {
            $this->log('Deleting portfolios badges tables...');
            $connection->getSchemaManager()->dropTable('icap__portfolio_widget_badges_badge');
            $connection->getSchemaManager()->dropTable('icap__portfolio_widget_badges');
            $this->log('Portfolios badges tables deleted.');
        }
    }
}
