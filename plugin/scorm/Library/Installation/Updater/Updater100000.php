<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/1/17
 */

namespace Claroline\ScormBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater100000 extends Updater
{
    private $container;
    protected $logger;
    private $om;

    public function __construct(ContainerInterface $container, $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->migrateProperties();
    }

    public function migrateProperties()
    {
        $classes = ['ClarolineScormBundle:Scorm12Resource', 'ClarolineScormBundle:Scorm2004Resource'];

        foreach ($classes as $class) {
            $entities = $this->om->getRepository($class)->findAll();
            $totalObjects = count($entities);
            $i = 0;
            $this->log("Adding properties for {$totalObjects} {$class}...");

            foreach ($entities as $entity) {
                $node = $entity->getResourceNode();
                $node->setFullscreen($entity->getHideTopBar());
                $node->getCloseTarget($entity->getExitMode());
                ++$i;

                $this->om->persist($entity);

                if ($i % 300 === 0) {
                    $this->log("Flushing [{$i}/{$totalObjects}]");
                    $this->om->flush();
                }
            }
        }

        $this->om->flush();
        $this->log('Clearing object manager...');
        $this->om->clear();
        $this->log('done !');
    }
}
