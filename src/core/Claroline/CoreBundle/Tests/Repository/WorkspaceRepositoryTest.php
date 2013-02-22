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
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user'));
        $this->wsRepo = $this->getEntityManager()
            ->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
    }

    public function testfindByUserReturnsExpectedResults()
    {
        $user = $this->getFixtureReference('user/user');
        $ws = $this->wsRepo->findByUser($user);

        $this->assertEquals(1, count($ws));
        $this->assertEquals($user->getPersonalWorkspace(), $ws[0]);

        $this->loadWorkspaceData(
            array(
                'Workspace_1' => 'user',
                'Workspace_2' => 'user',
                'Workspace_3' => 'user'
            )
        );
        $user->addRole(
            $this->getEntityManager()
                ->getRepository('ClarolineCoreBundle:Role')
                ->findCollaboratorRole($this->getWorkspace('Workspace_3'))
        );
        $this->getEntityManager()->flush();
        $userWs = $this->wsRepo->findByUser($user);
        $this->assertEquals(4, count($userWs));
        $this->assertEquals('Workspace_1', $userWs[1]->getName());
        $this->assertEquals('Workspace_2', $userWs[2]->getName());
    }
}