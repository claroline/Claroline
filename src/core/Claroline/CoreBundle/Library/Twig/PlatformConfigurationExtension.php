<?php

namespace Claroline\CoreBundle\Library\Twig;

use Twig_Extension;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class PlatformConfigurationExtension extends Twig_Extension
{
    private $configHandler;

    public function __construct(PlatformConfigurationHandler $handler)
    {
        $this->configHandler = $handler;
    }

    public function getGlobals()
    {
        return array(
            'config' => $this->configHandler
        );
    }

    public function getName()
    {
        return 'claro_platform_configuration';
    }
}