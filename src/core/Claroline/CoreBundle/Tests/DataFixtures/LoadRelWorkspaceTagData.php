<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;

class LoadRelWorkspaceTagData extends LoggableFixture
{
    private $relWorkspaceTag;

    public function __construct(array $relWorkspaceTag)
    {
        $this->relWorkspaceTag = $relWorkspaceTag;
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