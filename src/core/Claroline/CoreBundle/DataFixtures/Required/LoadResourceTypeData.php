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

        $defaultPerms = array(
            'open' => array(),
            'copy' => array(),
            'delete' => array('delete'),
            'export' => array('download'),
            'edit' => array('rename', 'properties', 'manage_rights')
        );

        $i = 0;

        foreach ($resourceTypes as $attributes) {
            $type = new ResourceType();
            $type->setName($attributes[0]);
            $type->setExportable($attributes[1]);
            $manager->persist($type);

            $j = 1;
            foreach($defaultPerms as $name => $menuActions) {

                $maskDecoder = new MaskDecoder();
                $maskDecoder->setValue(pow($j, 2));
                $maskDecoder->setName($name);
                $maskDecoder->setResourceType($type);

                foreach ($menuActions as $menuAction) {
                    $menu = new MenuAction();
                    $menu->setName($menuAction);
                    $menu->setAsync(true);
                    $menu->setPermRequired($name);
                    $menu->setResourceType($type);
                    $manager->persist($menu);
                }

                $manager->persist($maskDecoder);
                $j++;
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
