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

class Updater120212 extends Updater
{
    protected $logger;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->generateFieldFacetsUuids();
        $this->deleteSupportBundleFromDB();
    }

    public function generateFieldFacetsUuids()
    {
        $this->log('Rebuild fieldfacet uuids');
        $this->conn->prepare('UPDATE claro_field_facet SET uuid = (SELECT UUID())')->execute();
    }

    private function deleteSupportBundleFromDB()
    {
        $this->log('Deleting DB tables from SupportBundle...');
        $tablesSql = '
            DROP TABLE IF EXISTS
            formalibre_support_configuration,
            formalibre_support_comment,
            formalibre_support_ticket_user,
            formalibre_support_intervention,
            formalibre_support_ticket,
            formalibre_support_status,
            formalibre_support_type,
            doctrine_formalibresupportbundle_versions
        ';
        $this->conn->prepare($tablesSql)->execute();
        $this->log('DB tables from SupportBundle deleted.');

        $this->log('Deleting support tools...');
        $adminToolSql = '
            DELETE tool FROM claro_admin_tools tool
            WHERE tool.name = "formalibre_support_management_tool"
        ';
        $toolSql = '
            DELETE tool FROM claro_tools tool
            WHERE tool.name = "formalibre_support_tool"
        ';
        $this->conn->prepare($adminToolSql)->execute();
        $this->conn->prepare($toolSql)->execute();
        $this->log('Support tools deleted.');

        $this->log('Deleting SupportBundle plugin...');
        $pluginSql = '
            DELETE plugin FROM claro_plugin plugin
            WHERE plugin.vendor_name = "FormaLibre"
            AND plugin.short_name = "SupportBundle"
        ';
        $this->conn->prepare($pluginSql)->execute();
        $this->log('SupportBundle plugin deleted.');
    }
}
