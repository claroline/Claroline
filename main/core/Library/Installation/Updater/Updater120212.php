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
        $this->deleteSupportBundleTables();
    }

    public function generateFieldFacetsUuids()
    {
        $this->log('Rebuild fieldfacet uuids');
        $this->conn->prepare('UPDATE claro_field_facet SET uuid = (SELECT UUID())')->execute();
    }

    private function deleteSupportBundleTables()
    {
        $this->log('Deleting DB tables from SupportBundle...');
        $sql = '
            DROP TABLE IF EXISTS
            formalibre_support_configuration,
            formalibre_support_comment,
            formalibre_support_ticket_user,
            formalibre_support_intervention,
            formalibre_support_ticket,
            formalibre_support_status,
            formalibre_support_type
        ';
        $this->conn->prepare($sql)->execute();
        $this->log('DB tables from SupportBundle deleted.');
    }
}
