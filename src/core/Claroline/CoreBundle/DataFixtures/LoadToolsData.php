<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\Tool;

class LoadToolsData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface $container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $tools = array(
            array('home', 'icon-home', false, false, true, true, true),
            array('parameters', 'icon-cog', false, false, true, true, false),
            array('resource_manager', 'icon-folder-open', false, false, true, true, true),
            array('calendar', 'icon-calendar', false, false, true, true, false),
            array('logs', 'icon-list', false, false, true, true, false),
            array('user_management', 'icon-user', false, false, true, false, false),
            array('group_management', 'icon-th-list', false,  false, true, false, false)
        );

        foreach ($tools as $tool) {
            $entity = new Tool();
            $entity->setName($tool[0]);
            $entity->setClass($tool[1]);
            $entity->setIsWorkspaceRequired($tool[2]);
            $entity->setIsDesktopRequired($tool[3]);
            $entity->setDisplayableInWorkspace($tool[4]);
            $entity->setDisplayableInDesktop($tool[5]);
            $entity->setExportable($tool[6]);

            $manager->persist($entity);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 6;
    }

}
