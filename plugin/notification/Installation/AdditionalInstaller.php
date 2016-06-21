<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/13/15
 */

namespace Icap\NotificationBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Icap\NotificationBundle\Installation\Updater\Updater040200;

class AdditionalInstaller extends BaseInstaller
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '4.1.0', '<')) {
            $updater040200 = new Updater040200(
                $this->container->get('doctrine.orm.entity_manager'),
                $this->container->get('doctrine.dbal.default_connection')
            );
            $updater040200->setLogger($this->logger);
            $updater040200->postUpdate();
        }
    }
}
