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

use Claroline\CoreBundle\Library\Utilities\FileSystem;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater050200 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->util = $this->container->get('claroline.utilities.misc');
        $this->om = $this->container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateResourceNodes();
    }

    public function updateResourceNodes()
    {
        $this->log('Updating resource nodes guid');
        $nodes = $this->om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceNode')->findAll();
        $i = 0;
        
        foreach ($nodes as $node) {
            if ($node->getGuid() === null) {
                $node->setGuid($this->util->generateGuid());
                $this->om->persist($node);
                $i++;
            }
            
            if ($i % 50 === 0) {
                $this->log("{$i} resources flushed..."); 
                $this->om->flush();
            }
        }
        
        $this->om->flush();
        $this->log('Done !');    
    }
}
