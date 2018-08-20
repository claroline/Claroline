<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Innova\AudioRecorderBundle\Installation\Updater;

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
        $this->deactivateAudioRecorderResourceType();
    }

    private function deactivateAudioRecorderResourceType()
    {
        $resourceTypeRepo = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $audioRecorderType = $resourceTypeRepo->findOneBy(['name' => 'innova_audio_recorder']);

        if (!empty($audioRecorderType)) {
            $this->log('Deactivating Audio Recorder resource...');

            $audioRecorderType->setEnabled(false);
            $this->om->persist($audioRecorderType);
            $this->om->flush();

            $this->log('Audio Recorder resource is deactivated.');
        }
    }
}
