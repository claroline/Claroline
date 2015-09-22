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
         *   - icon class
         */
        $widgetTypes = array(
            array('userInformation', 'info'),
            array('text',            'align-left'),
            array('skills',          'bookmark'),
            array('formations',      'graduation-cap'),
            array('badges',          'trophy'),
            array('experience',      'briefcase')
        );

        foreach ($widgetTypes as $widgetType) {
            $entity = new WidgetType();
            $entity
                ->setName($widgetType[0])
                ->setIcon($widgetType[1]);

            $manager->persist($entity);
        }

        $manager->flush();
    }
}
