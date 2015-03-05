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

use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater050000
{
    private $container;
    private $conn;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->reactivateAllDisableDesktopHomeTools();
    }

    private function reactivateAllDisableDesktopHomeTools()
    {
        $this->log('Reactivating all disabled desktop home tools...');
        $updateReq = "
            UPDATE claro_ordered_tool
            SET is_visible_in_desktop = true
            WHERE (name = 'home' OR name = 'parameters')
            AND workspace_id IS NULL
            AND is_visible_in_desktop = false
        ";
        $this->conn->query($updateReq);
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}
