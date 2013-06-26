<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;

class LoadWorkspaceTagData extends LoggableFixture
{
    private $tags;

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->tags as $data) {
            $user = null;

            if (isset($data['user'])) {
                $user = $this->getReference('user/' . $data['user']);
            }

            $tag = $this->container->get('claroline.manager.workspace_tag_manager')->createTag(
                $data['name'],
                $user
            );

            $this->addReference('tag/' . $data['name'], $tag);
        }
        $manager->flush();
    }
}