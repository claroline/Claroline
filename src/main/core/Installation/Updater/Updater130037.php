<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130037 extends Updater
{
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(
        PlatformConfigurationHandler $config
    ) {
        $this->config = $config;
    }

    public function postUpdate()
    {
        $search = $this->config->getParameter('header.search');
        $newSearch = [
            'user' => true,
            'workspace' => true,
            'resource' => true,
        ];

        foreach (array_keys($newSearch) as $item) {
            if (isset($search[$item])) {
                $newSearch[$item] = $search[$item];
            }
        }

        $this->config->setParameter('search', [
            'limit' => 5,
            'items' => $newSearch,
        ]);
    }
}
