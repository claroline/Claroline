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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Tool\PwsToolConfig;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Resource\PwsRightsManagementAccess;
use Claroline\InstallationBundle\Updater\Updater;

class Updater040500 extends Updater
{
    private $container;
    private $om;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateOrder();
    }

    private function updateOrder()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $dirType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findByName('directory');
        $nodes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findBy(array('resourceType' => $dirType));
        $this->log('Updating resource order, this operation may take a while...');

        foreach ($nodes as $node) {
            if ($node->getResourceType()->getName() === 'directory') {
                $this->log('Updating ' . $node->getName() . ' resource order...');
                $this->container->get('claroline.manager.resource_manager')->reorder($node);
            }
        }
    }
}
