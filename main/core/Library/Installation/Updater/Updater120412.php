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

class Updater120412 extends Updater
{
    protected $logger;
    protected $conn;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->deleteDashboardBundleFromDB();
    }

    private function deleteDashboardBundleFromDB()
    {
        $this->log('Deleting DB tables from DashboardBundle...');
        $tablesSql = '
            DROP TABLE IF EXISTS
            claro_dashboard,
            doctrine_clarolinedashboardbundle_versions
        ';
        $this->conn->prepare($tablesSql)->execute();
        $this->log('DB tables from DashboardBundle deleted.');

        $this->log('Deleting dashboard tool...');
        $toolSql = '
            DELETE tool FROM claro_tools tool
            JOIN claro_plugin plugin on plugin.id = tool.plugin_id
            WHERE tool.name = "dashboard"
            AND plugin.vendor_name = "Claroline"
            AND plugin.short_name = "DashboardBundle"
        ';
        $this->conn->prepare($toolSql)->execute();
        $this->log('Dashboard tool deleted.');

        $this->log('Deleting DashboardBundle plugin...');
        $pluginSql = '
            DELETE plugin FROM claro_plugin plugin
            WHERE plugin.vendor_name = "Claroline"
            AND plugin.short_name = "DashboardBundle"
        ';
        $this->conn->prepare($pluginSql)->execute();
        $this->log('DashboardBundle plugin deleted.');
    }
}
