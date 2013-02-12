<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class RoleRepositoryTest extends FixtureTestCase
{
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->repo = $this->em->getRepository('ClarolineCoreBundle:Role');
    }

    public function testFindWorkspaceRole()
    {
        $this->loadPlatformRoleData();
        $this->loadUserData(array('john' => 'user'));
        $role = $this->repo->findWorkspaceRole($this->getUser('john'), $this->getWorkspace('john'));
        $this->assertEquals(0, strpos($role->getName(), 'ROLE_WS_MANAGER'));
    }
}