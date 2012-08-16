<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\MetaType;

/**
 * Resource types data fixture.
 */
class LoadResourceTypeData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface $container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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
        $documentMetatype->setName('document');
        $manager->persist($documentMetatype);
        $testThumb ='biblio_spiral.png';
        // resource type attributes : name, listable, navigable, class, download
        $resourceTypes = array(
            array('file', true, false, 'Claroline\CoreBundle\Entity\Resource\File', true, $testThumb),
            array('directory', true, true, 'Claroline\CoreBundle\Entity\Resource\Directory', true, $testThumb),
            array('text', true, false, 'Claroline\CoreBundle\Entity\Resource\Text', true, $testThumb)
        );

        foreach ($resourceTypes as $attributes) {
            $type = new ResourceType();
            $type->setType($attributes[0]);
            $type->setListable($attributes[1]);
            $type->setNavigable($attributes[2]);
            $type->setClass($attributes[3]);
            $type->setDownloadable($attributes[4]);
            $type->setThumbnail($attributes[5]);
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