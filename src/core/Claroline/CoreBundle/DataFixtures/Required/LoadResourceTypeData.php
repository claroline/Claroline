<?php

namespace Claroline\CoreBundle\DataFixtures\Required;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\MenuAction;

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
    public function load(ObjectManager $manager)
    {
        // resource type attributes : name, listable, navigable, class
        $resourceTypes = array(
            array('file', true),
            array('directory', true),
            array('text', true),
            array('resource_shortcut', false),
            array('activity', true)
        );

        foreach ($resourceTypes as $attributes) {
            $type = new ResourceType();
            $type->setName($attributes[0]);
            $type->setExportable($attributes[1]);
            $manager->persist($type);
            $this->container->get('claroline.manager.mask_manager')->addDefaultPerms($type);
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
