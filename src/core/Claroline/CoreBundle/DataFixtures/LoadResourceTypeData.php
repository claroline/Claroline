<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceTypeCustomAction;

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
            array('file', false, true, 'Claroline\CoreBundle\Entity\Resource\File'),
            array('directory', true, true, 'Claroline\CoreBundle\Entity\Resource\Directory'),
            array('text', false, true, 'Claroline\CoreBundle\Entity\Resource\Text'),
            array('resource_shortcut', false, false, 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut'),
            array('activity', false, true, 'Claroline\CoreBundle\Entity\Resource\Activity')
        );

        $i = 0;

        foreach ($resourceTypes as $attributes) {
            $type = new ResourceType();
            $type->setName($attributes[0]);
            $type->setBrowsable($attributes[1]);
            $type->setExportable($attributes[2]);
            $type->setClass($attributes[3]);
            $manager->persist($type);

            if (isset($customActions[$i])) {
                if ($customActions[$i] !== null) {
                    $actions = new ResourceTypeCustomAction();
                    $actions->setAction($customActions[$i][0]);
                    $actions->setAsync($customActions[$i][1]);
                    $actions->setResourceType($type);
                    $manager->persist($actions);
                }
            }

            $this->addReference("resource_type/{$attributes[0]}", $type);
            $i++;
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