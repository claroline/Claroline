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

//I assume this will be the first updater to distribution bundle.
class Updater070000 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container, $logger)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->logger = $logger;
    }

    public function postUpdate()
    {
        $pluginRepo = $this->om->getRepository('ClarolineCoreBundle:Plugin');
        $plugin = $pluginRepo->findOneBy(array('vendorName' => 'Claroline', 'bundleName' => 'VideoJsBundle'));

        if ($plugin) {
            $this->log('Removing VideoJsBundle plugin from database...');
            $this->om->remove($plugin);
            $this->om->flush();
        }

        $tool = $this->om->getRepository('Claroline\CoreBundle\Entity\Tool\AdminTool')->findOneByName('desktop_tools');

        if ($tool) {
            $this->log('Removing admin desktop tool from database...');
            $this->om->remove($tool);
            $this->om->flush();
        }
    }
}
