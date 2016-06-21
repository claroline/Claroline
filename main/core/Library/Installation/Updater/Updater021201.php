<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\InstallationBundle\Updater\Updater;

class Updater021201 extends Updater
{
    private $container;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct($container)
    {
        $this->container = $container;
        $this->objectManager = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $coreIconWebDirRelativePath = 'bundles/clarolinecore/images/resources/icons/';

        $images = array(
            array('res_mspowerpoint.png', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
            array('res_msword.png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        );

        $this->log('Adding new resource icons...');

        foreach ($images as $image) {
            $rimg = new ResourceIcon();
            $rimg->setRelativeUrl($coreIconWebDirRelativePath.$image[0]);
            $rimg->setMimeType($image[1]);
            $rimg->setShortcut(false);
            $this->objectManager->persist($rimg);
            $this->container->get('claroline.manager.icon_manager')
                ->createShortcutIcon($rimg);
        }

        $this->objectManager->flush();
    }
}
