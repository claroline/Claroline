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

class Updater120501 extends Updater
{
    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->updateWorkspaceRedirection();
    }

    public function updateWorkspaceRedirection()
    {
        $this->log('Updating workspace redirection');

        $data = [
          'platform_dashboard' => 'dashboard',
          'agenda_' => 'agenda',
          'resource_manager' => 'resources',
          'users' => 'community',
          'user_management' => 'community',
          'data_transfer' => 'transfer',
        ];

        foreach ($data as $old => $new) {
            $sql = "UPDATE claro_workspace_options SET details = REPLACE(details, '$old', '$new')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }
    }
}
