<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120000 extends Updater
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
        $this->deactivateResultResourceType();
    }

    private function deactivateResultResourceType()
    {
        $resourceTypeRepo = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $resultType = $resourceTypeRepo->findOneBy(['name' => 'claroline_result']);

        if (!empty($resultType)) {
            $this->log('Deactivating Result resource...');

            $resultType->setEnabled(false);
            $this->om->persist($resultType);
            $this->om->flush();

            $this->log('Result resource is deactivated.');
        }
    }
}
