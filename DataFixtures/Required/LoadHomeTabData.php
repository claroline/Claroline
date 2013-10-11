<?php

namespace Claroline\CoreBundle\DataFixtures\Required;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Platform Home tabs data fixture.
 */
class LoadHomeTabData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;

    /**
     * Loads the core Home Tabs.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $translator = $this->container->get('translator');
        $infoName = $translator->trans('informations', array(), 'platform');

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

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 9;
    }
}
