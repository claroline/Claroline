<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required\Data;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;

/**
 * Platform Home tabs data fixture.
 */
class LoadHomeTabData implements RequiredFixture
{
    /**
     * Loads the core Home Tabs.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $translator = $this->container->get('translator');
        $infoName = $translator->trans('informations', [], 'platform');

        $desktopHomeTab = new HomeTab();
        $desktopHomeTab->setType('admin_desktop');
        $desktopHomeTab->setName($infoName);
        $manager->persist($desktopHomeTab);

        $desktopHomeTabConfig = new HomeTabConfig();
        $desktopHomeTabConfig->setHomeTab($desktopHomeTab);
        $desktopHomeTabConfig->setType('admin_desktop');
        $desktopHomeTabConfig->setVisible(true);
        $desktopHomeTabConfig->setLocked(false);
        $desktopHomeTabConfig->setTabOrder(1);
        $manager->persist($desktopHomeTabConfig);

        $workspaceHomeTab = new HomeTab();
        $workspaceHomeTab->setType('admin_workspace');
        $workspaceHomeTab->setName($infoName);
        $manager->persist($workspaceHomeTab);

        $workspaceHomeTabConfig = new HomeTabConfig();
        $workspaceHomeTabConfig->setHomeTab($workspaceHomeTab);
        $workspaceHomeTabConfig->setType('admin_workspace');
        $workspaceHomeTabConfig->setVisible(true);
        $workspaceHomeTabConfig->setLocked(false);
        $workspaceHomeTabConfig->setTabOrder(1);
        $manager->persist($workspaceHomeTabConfig);
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
