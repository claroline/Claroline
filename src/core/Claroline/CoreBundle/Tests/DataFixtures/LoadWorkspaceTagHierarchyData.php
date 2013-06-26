<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;

class LoadWorkspaceTagHierarchyData extends LoggableFixture
{
    private $tagHierarchies;

    public function __construct(array $tagHierarchies)
    {
        $this->tagHierarchies = $tagHierarchies;
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->tagHierarchies as $data) {
            $child = $this->getReference('tag/' . $data['child']);
            $parent = $this->getReference('tag/' . $data['parent']);

            $this->container->get('claroline.manager.workspace_tag_manager')->createTagHierarchy(
                $child,
                $parent,
                $data['level']
            );
        }
        $manager->flush();
    }
}