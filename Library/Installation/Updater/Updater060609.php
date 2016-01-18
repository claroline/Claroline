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

class Updater060609 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->manager = $this->container->get('claroline.persistence.object_manager');
        $this->repo = $this->manager->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $this->iconManager = $this->container->get('claroline.manager.icon_manager');
    }

    public function postUpdate()
    {
        $this->refreshIconShortcuts();
    }

    private function refreshIconShortcuts()
    {
        $this->log('Updating icons...');
        //todo: method in repository
        $baseIcons = $this->repo->findBaseIcons();
        $this->manager->startFlushSuite();

        foreach ($baseIcons as $icon) {
            $this->log('Refreshing ' . $icon->getMimeType() . '...');
            $this->iconManager->refresh($icon);
        }

        $this->manager->endFlushSuite();
        $this->manager->forceFlush();
    }
}
