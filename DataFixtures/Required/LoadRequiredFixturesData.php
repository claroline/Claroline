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
            array('userInformation', true,  'info'),
            array('text',            false, 'align-left'),
            array('skills',          false, 'bookmark'),
            array('formations',      false, 'graduation-cap'),
            array('badges',          false, 'trophy'),
            array('experience',      false, 'briefcase')
        );

        foreach ($widgetTypes as $widgetType) {
            $entity = new WidgetType();
            $entity
                ->setName($widgetType[0])
                ->setIsUnique($widgetType[1])
                ->setIcon($widgetType[2]);

            $manager->persist($entity);
        }

        $manager->flush();
    }
}
