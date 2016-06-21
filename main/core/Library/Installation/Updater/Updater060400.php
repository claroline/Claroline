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

class Updater060400 extends Updater
{
    private $container;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->ut = $this->container->get('claroline.utilities.misc');
    }

    public function postUpdate()
    {
        $classes = array(
            'Claroline\CoreBundle\Entity\User',
            'Claroline\CoreBundle\Entity\Group',
            'Claroline\CoreBundle\Entity\Resource\ResourceNode',
        );

        foreach ($classes as $class) {
            $this->setGuidForClass($class);
        }
    }

    public function setGuidForClass($class)
    {
        $entities = $this->om->getRepository($class)->findAll();
        $totalObjects = count($entities);
        $i = 0;
        $this->log("Adding user guids for {$totalObjects} {$class}...");

        foreach ($entities as $entity) {
            if (!$entity->getGuid()) {
                $entity->setGuid($this->ut->generateGuid());
                $this->om->persist($entity);
            }
            ++$i;

            if ($i % 300 === 0) {
                $this->log("Flushing [{$i}/{$totalObjects}]");
                $this->om->flush();
            }
        }

        $this->om->flush();
        $this->log("Guid added for {$totalObjects} {$class} !");
        $this->log('Clearing object manager...');
        $this->om->clear();
        $this->log('done !');
    }
}
