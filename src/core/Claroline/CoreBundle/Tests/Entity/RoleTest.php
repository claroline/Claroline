<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\DataFixtures\LoadPlatformRolesData;

class RoleTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixture(new LoadPlatformRolesData());
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
        $this->getFixtureReference('role/admin')->setName('ROLE_FOO');
    }

    public function testPlatformRoleCannotBeDeleted()
    {
        $roleUser = $this->getFixtureReference('role/user');
        $this->assertTrue($roleUser->isReadOnly());

        $this->setExpectedException('RuntimeException');
        $this->getEntityManager()->remove($roleUser);
    }
}