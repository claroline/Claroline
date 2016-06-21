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

class Updater020304 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $ds = DIRECTORY_SEPARATOR;
        $coreWebDir = "{$this->container->getParameter('kernel.root_dir')}{$ds}..{$ds}web{$ds}";
        $coreIconWebDirRelativePath = 'bundles/clarolinecore/images/resources/icons/';
        $coreIconWebDir = "{$coreWebDir}bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}";
        $resourceImage = array('res_audio.png', 'audio');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $this->log('Update images...');
        $rimg = new ResourceIcon();
        $rimg->setRelativeUrl($coreIconWebDirRelativePath.$resourceImage[0]);
        $rimg->setMimeType($resourceImage[1]);
        $rimg->setShortcut(false);
        $em->persist($rimg);

        $this->container->get('claroline.manager.icon_manager')
            ->createShortcutIcon($rimg);
    }
}
