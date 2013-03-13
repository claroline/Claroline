<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class RoleTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
    }

    public function testRoleNamesMustFollowTheSymfonyConvention()
    {
        $this->setExpectedException('RuntimeException');
        $role = new Role();
        $role->setName('WRONG_PREFIX_ROLE');
    }

    public function testPlatformRoleCannotBeModified()
    {
        $this->setExpectedException('RuntimeException');
        $this->getRole('admin')->setName('ROLE_FOO');
    }

    public function testPlatformRoleCannotBeDeleted()
    {
        $roleUser = $this->getRole('user');
        $this->assertTrue($roleUser->isReadOnly());

        $this->setExpectedException('RuntimeException');
        $this->getEntityManager()->remove($roleUser);
    }
}