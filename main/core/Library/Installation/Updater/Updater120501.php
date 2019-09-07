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
    private $container;
    private $conn;
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateWorkspaceRedirection();
        $this->removeTool('dashboard');
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

    private function removeTool($toolName)
    {
        $this->log(sprintf('Removing `%s` tool...', $toolName));

        $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneBy(['name' => $toolName]);
        if (!empty($tool)) {
            $this->om->remove($tool);
            $this->om->flush();
        }

        $sql = "DELETE FROM claro_ordered_tool WHERE name = '${toolName}'";

        $this->log($sql);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }
}
