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
use Symfony\Component\Filesystem\Filesystem;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;

class Updater050003 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->log('Moving default template...');

        $fileDir = $this->container->getParameter('claroline.param.files_directory');
        $defaultTemplate = $this->container->getParameter('claroline.param.default_template');
        $newTemplateDir = $fileDir . '/templates';
        $newTemplate = $newTemplateDir . '/default.zip';

        $fs = new Filesystem();
        $fs->mkdir($newTemplateDir);
        $fs->copy($defaultTemplate, $newTemplate);

        $this->addingResourceIcons();
    }

    private function addingResourceIcons()
    {
        $coreIconWebDirRelativePath = "bundles/clarolinecore/images/resources/icons/";
        $om = $this->container->get('doctrine.orm.entity_manager');
        $repo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $resourceImages = $this->container->get('claroline.manager.icon_manager')->getDefaultIconMap();

        foreach ($resourceImages as $resourceImage) {
            $imgs = $repo->findBy(array('mimeType' => $resourceImage[1]));

            if (count($imgs) === 0) {
                $this->log('Adding icon for mime type ' . $resourceImage[1] . '...');
                $rimg = new ResourceIcon();
                $rimg->setRelativeUrl($coreIconWebDirRelativePath . $resourceImage[0]);
                $rimg->setMimeType($resourceImage[1]);
                $rimg->setShortcut(false);
                $om->persist($rimg);

                $this->container->get('claroline.manager.icon_manager')
                    ->createShortcutIcon($rimg);
            }
        }
    }
}
