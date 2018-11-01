<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\Route("/maintenance", options={"expose" = true})
 */
class MaintenanceController
{
    /**
     * PlatformListener constructor.
     *
     * @DI\InjectParams({
     *     "config" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param PlatformConfigurationHandler $config
     */
    public function __construct(
      PlatformConfigurationHandler $config
  ) {
        $this->config = $config;
    }

    /**
     * @EXT\Route("/alert", name="claroline_maintenance_alert")
     * @EXT\Template("ClarolineCoreBundle:maintenance:alert.html.twig")
     *
     * @return array
     */
    public function alertAction()
    {
        return [
            'message' => $this->config->getParameter('maintenance.message'),
        ];
    }
}
