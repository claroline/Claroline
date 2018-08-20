<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\LtiBundle\Library\Installation\Updater;

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
        $this->deactivateLtiResourceType();
    }

    private function deactivateLtiResourceType()
    {
        $resourceTypeRepo = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $ltiType = $resourceTypeRepo->findOneBy(['name' => 'ujm_lti_resource']);

        if (!empty($ltiType)) {
            $this->log('Deactivating LTI resource...');

            $ltiType->setEnabled(false);
            $this->om->persist($ltiType);
            $this->om->flush();

            $this->log('LTI resource is deactivated.');
        }
    }
}
