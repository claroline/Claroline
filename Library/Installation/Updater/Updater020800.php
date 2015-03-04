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

class Updater020800 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->dropOldTables();
        $this->copyMailerParameters();
    }

    private function dropOldTables()
    {
        $this->log('Removing old claro_link table...');
        $conn = $this->container->get('doctrine.dbal.default_connection');
        $conn->exec('DROP TABLE IF EXISTS claro_link');
        $this->log('Removing old claro_resource_type_custom_action...');
        $conn->exec('DROP TABLE IF EXISTS claro_resource_type_custom_action');
    }

    private function copyMailerParameters()
    {
        $this->log('Copying mailer parameters...');
        $configHandler = $this->container->get('claroline.config.platform_config_handler');
        $copyParameter = function ($shortParameterName, $shortOptionName) use ($configHandler) {
            $parameterValue = $this->container->getParameter("mailer_{$shortParameterName}");
            $configHandler->setParameter("mailer_{$shortOptionName}", $parameterValue);
        };
        $copyParameter('transport', 'transport');
        $copyParameter('encryption', 'encryption');
        $copyParameter('host', 'host');
        $copyParameter('user', 'username');
        $copyParameter('password', 'password');
        $copyParameter('auth_mode', 'auth_mode');
    }
}
