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
            array('resource_manager', 'resource_small.png', true, Tool::WORKSPACE_AND_DESKTOP, 'resources'),
            array('agenda', 'calendar_small.png', false, Tool::WORKSPACE_AND_DESKTOP, 'agenda'),
            array('parameters', 'process_small.png', true, Tool::WORKSPACE_AND_DESKTOP, 'parameters'),
            array('user_management', 'user_small.png', true, Tool::WORKSPACE_ONLY, 'user_management'),
            array('group_management', 'users_small.png', true,  Tool::WORKSPACE_ONLY, 'group_management'),
            array('home', 'home_small.png', true, Tool::WORKSPACE_AND_DESKTOP, 'home' )
        );

        foreach ($tools as $tool) {
            $entity = new Tool();
            $entity->setName($tool[0]);
            $entity->setIcon($basePath.$tool[1]);
            $entity->setIsWorkspaceRequired($tool[2]);
            $entity->setDisplayability($tool[3]);
            $entity->setTranslationKey($tool[4]);

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