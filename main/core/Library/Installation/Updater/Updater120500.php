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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120500 extends Updater
{
    protected $logger;
    private $container;
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $configHandler;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->configHandler = $container->get('claroline.config.platform_config_handler');
    }

    public function postUpdate()
    {
        $this->updatePlatformOptions();

        $this->removeTool('my_contacts');
        $this->removeTool('workspace_management');
    }

    private function updatePlatformOptions()
    {
        $header = $this->configHandler->getParameter('header_menu');
        if (!empty($header)) {
            $this->configHandler->setParameter('header_menu', [
                'search',
                'history',
                'favourites',
            ]);
        }
    }

    private function removeTool($toolName, $admin = false)
    {
        $this->log(sprintf('Removing `%s` tool...', $toolName));

        $tool = $this->om->getRepository($admin ? 'ClarolineCoreBundle:Tool\AdminTool' : 'ClarolineCoreBundle:Tool\Tool')->findOneBy(['name' => $toolName]);
        if (!empty($tool)) {
            $this->om->remove($tool);
            $this->om->flush();
        }
    }
}
