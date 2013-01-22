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
        $ws = $this->wsRepo->getWorkspacesOfUser($user);

        $this->assertEquals(1, count($ws));
        $this->assertEquals($user->getPersonalWorkspace(), $ws[0]);

        $this->createWorkspace('Workspace 1', $user);
        $this->createWorkspace('Workspace 2', $user);
        $thirdWs = $this->createWorkspace('Workspace 3', $user);
        $user->addRole(
            $this->getEntityManager()
                ->getRepository('ClarolineCoreBundle:Role')
                ->getCollaboratorRole($thirdWs)
        );
        $this->getEntityManager()->flush();
        $userWs = $this->wsRepo->getWorkspacesOfUser($user);
        $this->assertEquals(4, count($userWs));
        $this->assertEquals('Workspace 1', $userWs[1]->getName());
        $this->assertEquals('Workspace 2', $userWs[2]->getName());
    }

    private function createWorkspace($name, $user)
    {
        $config = new Configuration();
        $config->setWorkspaceName($name);
        $config->setWorkspaceCode('code');
        $wsCreator = $this->client->getContainer()->get('claroline.workspace.creator');

        return $wsCreator->createWorkspace($config, $user);
    }
}