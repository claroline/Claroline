<?php
namespace Claroline\CoreBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Claroline\CoreBundle\Entity\Workspace;

class LoadWorkspaceData implements FixtureInterface
{

    public function load($manager) {
        for($i = 0; $i < 10; ++$i)
        {
            $workspace = new Workspace();
            $workspace->setName("test workspace #{$i}");
            $manager->persist($workspace);
        }

        $manager->flush();
    }

}
