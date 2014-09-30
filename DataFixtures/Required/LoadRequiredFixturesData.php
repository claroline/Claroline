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
            array('title',           true,  false, null),
            array('userInformation', true,  true,  'info'),
            array('presentation',    true,  true,  'comment'),
            array('skills',          false, true,  'bookmark'),
            array('formations',      false, true,  'graduation-cap'),
            array('badges',          false, true,  'trophy')
        );

        foreach ($widgetTypes as $widgetType) {
            $entity = new WidgetType();
            $entity
                ->setName($widgetType[0])
                ->setIsUnique($widgetType[1])
                ->setIsDeletable($widgetType[2])
                ->setIcon($widgetType[3]);

            $manager->persist($entity);
        }

        $manager->flush();
    }
}
