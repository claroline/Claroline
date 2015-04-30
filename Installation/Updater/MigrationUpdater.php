<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MigrationUpdater extends Updater
{
    private $container;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('doctrine.orm.entity_manager');
    }

    public function preUpdate()
    {
        $this->log('Updating migration versions...');
        $conn = $this->om->getConnection();
        $stmt = $conn->query("show tables");
        $found = false;

        while ($row = $stmt->fetch()) {
            foreach ($row as $key => $val) {
                if ($val === 'claro_event') $found = true;
            }
        }

        if (!$found) {
            $this->log('Inserting migration 20150429110105');
            $conn->query("INSERT INTO doctrine_clarolineagendabundle_versions (version) VALUES (20150429110105)");
        } else {
            $this->log('Migrations found.');
        }

    }
}
