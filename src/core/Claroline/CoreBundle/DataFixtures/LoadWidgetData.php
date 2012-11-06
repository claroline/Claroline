<?php

namespace Claroline\CoreBundle\DataFixtures;

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
        $defaultIcon = "bundles/clarolinecore/images/resources/icons/large/res_default.png";

        //name, isConfigurable, icon
        $items = array(
            array('core_resource_logger', false, $defaultIcon)
        );

        foreach ($items as $item) {
            $widget = new Widget();
            $widget->setName($item[0]);
            $widget->setConfigurable($item[1]);
            $widget->setIcon($item[2]);
            $widget->setPlugin(null);
            $manager->persist($widget);

            $widgetConfig = new DisplayConfig();
            $widgetConfig->setWidget($widget);
            $widgetConfig->setLock(true);
            $widgetConfig->setVisible(true);
            $widgetConfig->setParent(null);
            $manager->persist($widgetConfig);
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