<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Testing\FixtureTestCase;
use Claroline\CoreBundle\DataFixtures\LoadPlatformRolesData;

class RoleTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixture(new LoadPlatformRolesData());
    }
    
    public function testPlatformRoleCannotBeModified()
    {
        $this->setExpectedException('Claroline\CoreBundle\Exception\ClarolineException');
        $this->getFixtureReference('role/admin')->setName('ROLE_FOO');
    }
    
    public function testPlatformRoleCannotBeDeleted()
    {
        $roleUser = $this->getFixtureReference('role/user');
        $this->assertFalse($roleUser->canBeDeleted());
        
        $this->setExpectedException('Claroline\CoreBundle\Exception\ClarolineException');
        $this->getEntityManager()->remove($roleUser);
    }
}