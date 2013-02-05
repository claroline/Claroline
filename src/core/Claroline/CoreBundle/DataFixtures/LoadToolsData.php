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
        $ds = DIRECTORY_SEPARATOR;
        $basePath = "bundles{$ds}clarolinecore{$ds}images{$ds}workspace{$ds}tools{$ds}";

        $tools = array(
            array('home', 'home_small.png', false, false, Tool::WORKSPACE_AND_DESKTOP, 'home' ),
            array('parameters', 'process_small.png', false, false, Tool::WORKSPACE_AND_DESKTOP, 'parameters'),
            array('resource_manager', 'resource_small.png', false, false, Tool::WORKSPACE_AND_DESKTOP, 'resources'),
            array('calendar', 'calendar_small.png', false, false, Tool::WORKSPACE_AND_DESKTOP, 'agenda'),
            array('user_management', 'user_small.png', false, false, Tool::WORKSPACE_ONLY, 'user_management'),
            array('group_management', 'users_small.png', false,  false, Tool::WORKSPACE_ONLY, 'group_management')
        );

        foreach ($tools as $tool) {
            $entity = new Tool();
            $entity->setName($tool[0]);
            $entity->setIcon($basePath.$tool[1]);
            $entity->setIsWorkspaceRequired($tool[2]);
            $entity->setIsDesktopRequired($tool[3]);
            $entity->setDisplayability($tool[4]);
            $entity->setTranslationKey($tool[5]);

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