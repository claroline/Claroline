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
        $user = $this->getUser('user');
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

    public function testfindByRolesResturnsExcectedResults()
    {
        $this->loadUserData(array('creator' => 'ws_creator'));
        $user = $this->getUser('user');
        $this->loadGroupData(array('group_a' => array('user')));
        $group = $this->getGroup('group_a');
        $roleRepo = $this->em->getRepository('ClarolineCoreBundle:Role');

        $this->loadWorkspaceData(
            array(
                'Workspace_1' => 'creator',
                'Workspace_2' => 'creator',
                'Workspace_3' => 'creator'
            )
        );

        $user->addRole($roleRepo->findCollaboratorRole($this->getWorkspace('Workspace_1')));
        $group->addRole($roleRepo->findCollaboratorRole($this->getWorkspace('Workspace_2')));
        $this->em->persist($user);
        $this->em->persist($group);
        $this->em->flush();

        $ws = $this->em
            ->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->findByRoles($user->getRoles());

        $this->assertEquals(3, count($ws));
    }
}