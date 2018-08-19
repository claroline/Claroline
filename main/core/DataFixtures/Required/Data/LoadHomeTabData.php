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
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;

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
        $desktopHomeTab->setType('administration');
        $manager->persist($desktopHomeTab);

        $desktopHomeTabConfig = new HomeTabConfig();
        $desktopHomeTabConfig->setHomeTab($desktopHomeTab);
        $desktopHomeTabConfig->setType(HomeTab::TYPE_ADMIN_DESKTOP);
        $desktopHomeTabConfig->setVisible(true);
        $desktopHomeTabConfig->setLocked(false);
        $desktopHomeTabConfig->setTabOrder(1);
        $desktopHomeTabConfig->setName($infoName);
        $desktopHomeTabConfig->setLongTitle($infoName);
        $manager->persist($desktopHomeTabConfig);
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
