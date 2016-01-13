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
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Library\Utilities\FileSystem;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater060608 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->manager = $this->container->get('claroline.persistence.object_manager');
        $this->repo = $this->manager->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
    }

    public function postUpdate()
    {
        $this->addXlsMimeType();
    }

    private function addXlsMimeType()
    {
        $this->log('Adding xls extension mime types for resource icons...');
        $coreIconWebDirRelativePath = "bundles/clarolinecore/images/resources/icons/";

        $resourceImages = $this->container->get('claroline.manager.icon_manager')->getDefaultIconMap();

        foreach ($resourceImages as $resourceImage) {
            $mimeType = $resourceImage[1];
            $results = $this->repo->findByMimeType($mimeType);

            if (count($results) === 0) {
                $this->log('Adding mime type for ' . $mimeType . '.');
                $rimg = new ResourceIcon();
                $rimg->setRelativeUrl($coreIconWebDirRelativePath . $resourceImage[0]);
                $rimg->setMimeType($mimeType);
                $rimg->setShortcut(false);
                $this->manager->persist($rimg);

                $this->container->get('claroline.manager.icon_manager')
                    ->createShortcutIcon($rimg);
            }
        }
    }
}
