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

class Updater120413 extends Updater
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
        $this->deleteToolFromDB('analytics');
        $this->deleteToolFromDB('logs');
        $this->deleteToolFromDB('progression');
        $this->deleteAdminToolFromDB('platform_analytics');
        $this->deleteAdminToolFromDB('platform_logs');
    }

    private function deleteToolFromDB($toolName)
    {
        $this->log("Deleting $toolName tool...");
        $toolSql = '
            DELETE tool FROM claro_tools tool
            WHERE tool.name = "'.$toolName.'"
        ';
        $this->conn->prepare($toolSql)->execute();
        $this->log("$toolName tool deleted.");
    }

    private function deleteAdminToolFromDB($toolName)
    {
        $this->log("Deleting $toolName admin tool...");
        $adminToolSql = '
            DELETE tool FROM claro_admin_tools tool
            WHERE tool.name = "'.$toolName.'"
        ';
        $this->conn->prepare($adminToolSql)->execute();
        $this->log("$toolName admin tool deleted.");
    }
}
