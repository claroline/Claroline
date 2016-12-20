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

class DocUpdater extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function updateDocUrl($url)
    {
        $this->container->get('claroline.config.platform_config_handler')->setParameter('help_url', $url);
    }
}
