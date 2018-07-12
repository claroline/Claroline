<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\DropzoneBundle\Library\Installation\Updater;

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
        $this->deactivateResourceType();
    }

    private function deactivateResourceType()
    {
        $resourceTypeRepo = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $dropzoneType = $resourceTypeRepo->findOneBy(['name' => 'icap_dropzone']);

        if (!empty($dropzoneType)) {
            $this->log('Deactivating old Dropzone resources...');

            $dropzoneType->setEnabled(false);
            $this->om->persist($dropzoneType);

            $this->om->flush();
            $this->log('Old Dropzone is deactivated.');
        }
    }
}
