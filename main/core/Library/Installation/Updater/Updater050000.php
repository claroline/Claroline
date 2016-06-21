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

class Updater050000 extends Updater
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
        $stmt = $conn->query('SELECT * from doctrine_clarolinecorebundle_versions where version=20150428152724');
        $found = false;

        while ($row = $stmt->fetch()) {
            $found = true;
        }

        if (!$found) {
            $this->log('Inserting migration 20150428152724.');
            $conn->query('INSERT INTO doctrine_clarolinecorebundle_versions (version) VALUES (20150428152724)');
        } else {
            $this->log('Migrations found.');
        }
    }
}
