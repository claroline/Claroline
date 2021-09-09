<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130032 extends Updater
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
        $header = $this->config->getParameter('header');

        $newHeader = [];
        foreach ($header as $order => $menuName) {
            if (is_string($menuName)) {
                $newHeader[$menuName] = [
                    'order' => $order,
                ];

                if ('search' === $menuName) {
                    $newHeader[$menuName] = array_merge($newHeader[$menuName], $this->config->getParameter('header_search'));
                }
            }
        }

        $this->config->setParameter('header', $newHeader);
    }
}
