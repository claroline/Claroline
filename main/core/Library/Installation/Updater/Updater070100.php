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

class Updater070100 extends Updater
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
        $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll(false);
        $this->log('Enabling resource types...');

        foreach ($resourceTypes as $resourceType) {
            $this->log("Enabling {$resourceType->getName()}");
            $resourceType->setIsEnabled(true);
            $this->om->persist($resourceType);
        }

        $this->om->flush();
    }
}
