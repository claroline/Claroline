<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\MetaType;

/**
 * Resource types data fixture.
 */
class LoadResourceTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Loads one meta type (document) and four resource types handled by the platform core :
     * - File
     * - Directory
     * - Link
     * - Text
     * All these resource types have the 'document' meta type.
     *
     * @param ObjectManager $manager
     */
    public function load (ObjectManager $manager)
    {
        $documentMetatype = new MetaType();
        $documentMetatype->setMetaType('document');
        $manager->persist($documentMetatype);

        // resource type attributes : name, listable, navigable, class
        $resourceTypes = array(
            array('file', true, false, 'Claroline\CoreBundle\Entity\Resource\File'),
            array('directory', true, true, 'Claroline\CoreBundle\Entity\Resource\Directory'),
            array('link', true, false, 'Claroline\CoreBundle\Entity\Resource\Link'),
            array('text', true, false, 'Claroline\CoreBundle\Entity\Resource\Text')
        );

        foreach ($resourceTypes as $attributes) {
            $type = new ResourceType();
            $type->setType($attributes[0]);
            $type->setListable($attributes[1]);
            $type->setNavigable($attributes[2]);
            $type->setClass($attributes[3]);
            $type->addMetaType($documentMetatype);
            $manager->persist($type);
            $this->addReference("resource_type/{$attributes[0]}", $type);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}