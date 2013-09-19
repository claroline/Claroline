<?php

namespace Claroline\CoreBundle\DataFixtures\Required;

use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Platform widgets data fixture.
 */
class LoadWidgetData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Loads the core widgets.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        //name, isConfigurable, isDisplayableInDesktop, isDisplayableInWorkspace
        $items = array(
            array('core_resource_logger', true, true, true),
            array('simple_text', true, true, true),
            array('my_workspaces', false, true, false),
        );

        foreach ($items as $item) {
            $widget = new Widget();
            $widget->setName($item[0]);
            $widget->setConfigurable($item[1]);
            $widget->setIcon('fake/icon/path');
            $widget->setPlugin(null);
            $widget->setExportable(false);
            $widget->setDisplayableInDesktop($item[2]);
            $widget->setDisplayableInWorkspace($item[3]);
            $manager->persist($widget);

            if ($item[3]) {
                $wWidgetConfig = new DisplayConfig();
                $wWidgetConfig->setWidget($widget);
                $wWidgetConfig->setLock(false);
                $wWidgetConfig->setVisible(true);
                $wWidgetConfig->setParent(null);
                $wWidgetConfig->setDesktop(false);
                $wWidgetConfig->setName($item[0]);
                $manager->persist($wWidgetConfig);
            }

            if ($item[2]) {
                $dWidgetConfig = new DisplayConfig();
                $dWidgetConfig->setWidget($widget);
                $dWidgetConfig->setLock(false);
                $dWidgetConfig->setVisible(true);
                $wWidgetConfig->setParent(null);
                $dWidgetConfig->setDesktop(true);
                $dWidgetConfig->setName($item[0]);
                $manager->persist($dWidgetConfig);
            }
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5;
    }
}
