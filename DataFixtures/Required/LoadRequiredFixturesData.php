<?php

namespace Icap\PortfolioBundle\DataFixtures\Required;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Icap\PortfolioBundle\Entity\Widget\WidgetType;

class LoadRequiredFixturesData extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /**
         * array format:
         *   - name
         *   - is_unique
         *   - is_deletable
         */
        $widgetTypes = array(
            array('title', true, false),
            array('userInformation', true, true),
            array('skills', false, true),
            array('presentation', true, true)
        );

        foreach ($widgetTypes as $widgetType) {
            $entity = new WidgetType();
            $entity
                ->setName($widgetType[0])
                ->setIsUnique($widgetType[1])
                ->setIsDeletable($widgetType[2]);

            $manager->persist($entity);
        }

        $manager->flush();
    }
}
