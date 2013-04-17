<?php

namespace Claroline\CoreBundle\Library\Twig;

use Twig_Extension;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * Adds the PlatformConfigurationHandler to the Twig Globals.
 *
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class PlatformConfigurationExtension extends Twig_Extension
{
    private $configHandler;

    /**
     * @DI\InjectParams({
     *     "handler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
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