<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;

class LoadRelWorkspaceTagData extends LoggableFixture implements ContainerAwareInterface
{
    private $container;
    private $relWorkspaceTag;

    public function __construct(array $relWorkspaceTag)
    {
        $this->relWorkspaceTag = $relWorkspaceTag;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->relWorkspaceTag as $data) {
            $workspace = $this->getReference('workspace/' . $data['workspace']);

            foreach ($data['tags'] as $tagName) {
                $tag = $this->getReference('tag/' . $tagName);

                $this->container->get('claroline.manager.workspace_tag_manager')->createTagRelation(
                    $tag,
                    $workspace
                );
            }
        }
        $manager->flush();
    }
}