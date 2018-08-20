<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Innova\VideoRecorderBundle\Installation\Updater;

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
        $this->deactivateVideoRecorderResourceType();
    }

    private function deactivateVideoRecorderResourceType()
    {
        $resourceTypeRepo = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $videoRecorderType = $resourceTypeRepo->findOneBy(['name' => 'innova_video_recorder']);

        if (!empty($videoRecorderType)) {
            $this->log('Deactivating Video Recorder resource...');

            $videoRecorderType->setEnabled(false);
            $this->om->persist($videoRecorderType);
            $this->om->flush();

            $this->log('Video Recorder resource is deactivated.');
        }
    }
}
