<?php

namespace Innova\PathBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\InstallationBundle\Updater\Updater;

class Updater110000 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Removes unused mask decoder.
     */
    public function postUpdate()
    {
        /** @var ObjectManager $om */
        $om = $this->container->get('claroline.persistence.object_manager');

        /** @var MaskManager $maskManager */
        $maskManager = $this->container->get('claroline.manager.mask_manager');

        /** @var ResourceType $pathType */
        $pathType = $om
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(['name' => 'innova_path']);

        $this->log('Removing unused mask decoder `path_administrate`...');
        $maskManager->removeMask($pathType, 'path_administrate');

        $this->log('Renaming mask decoder `manageresults` to `manage_results`...');
        $maskManager->renameMask($pathType, 'manageresults', 'manage_results');

        $om->flush();
    }
}
