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

use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceIconsUpdater extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $this->container->get('claroline.persistence.object_manager');
        $this->repo = $this->om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $this->iconManager = $this->container->get('claroline.manager.icon_manager');
    }

    public function postUpdate()
    {
        $this->updateIcons();
    }

    private function updateIcons()
    {
        $this->log('Refreshing mime types icons...');
        $coreIconWebDirRelativePath = "bundles/clarolinecore/images/resources/icons/";

        $resourceImages = $this->container->get('claroline.manager.icon_manager')->getDefaultIconMap();
        $this->om->startFlushSuite();

        foreach ($resourceImages as $resourceImage) {
            $mimeType = $resourceImage[1];
            $results = $this->repo->findBy(array('mimeType' => $mimeType, 'isShortcut' => false));
            $rimg = count($results > 0) ? $results[0]: null;

            if ($rimg === null) {
                $this->log('Adding mime type for ' . $mimeType . '.');
                $rimg = new ResourceIcon();
                $rimg->setMimeType($mimeType);
                $rimg->setShortcut(false);
                $this->container->get('claroline.manager.icon_manager')->createShortcutIcon($rimg);
            } 

            $rimg->setRelativeUrl($coreIconWebDirRelativePath . $resourceImage[0]);
            $this->om->persist($rimg);
        }

        $this->om->endFlushSuite();
        $baseIcons = $this->repo->findBaseIcons();
        $this->om->startFlushSuite();

        foreach ($baseIcons as $icon) {
            $this->log('Refreshing ' . $icon->getMimeType() . '...');
            $this->iconManager->refresh($icon);
        }

        $this->om->endFlushSuite();
        $this->om->forceFlush();
    }
}
