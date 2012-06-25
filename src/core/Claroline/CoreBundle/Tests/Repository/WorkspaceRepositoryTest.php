<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\Library\Workspace\Configuration;

class WorkspaceRepositoryTest extends FixtureTestCase
{
    /** @var WorkspaceRepository */
    private $wsRepo;

    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->wsRepo = $this->getEntityManager()
            ->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
    }

    public function testGetWorkspacesOfUserReturnsExpectedResults()
    {
        $user = $this->getFixtureReference('user/user');

        $this->assertEquals(array(), $this->wsRepo->getWorkspacesOfUser($user));

        $this->createWorkspace('Workspace 1');
        $this->createWorkspace('Workspace 2', $user);
        $thirdWs = $this->createWorkspace('Workspace 3');
        $user->addRole($thirdWs->getCollaboratorRole());
        $this->getEntityManager()->flush();

        $userWs = $this->wsRepo->getWorkspacesOfUser($user);
        $this->assertEquals(2, count($userWs));
        $this->assertEquals('Workspace 2', $userWs[0]->getName());
        $this->assertEquals('Workspace 3', $userWs[1]->getName());
    }

    private function createWorkspace($name, $user = null)
    {
        $config = new Configuration();
        $config->setWorkspaceName($name);
        $wsCreator = $this->client->getContainer()->get('claroline.workspace.creator');

        return $wsCreator->createWorkspace($config, $user);
    }
}