<?php

namespace Claroline\CoreBundle\Security\Voter;

use Claroline\CoreBundle\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Security\Acl\ClassIdentity;
use Claroline\CoreBundle\Security\PlatformRoles;

class AdministratorVoterTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
    }
    
    public function testAdministrorIsAlwaysGranted()
    {
        $admin = $this->getFixtureReference('user/admin');
        
        $this->logUser($admin);
        $security = $this->getSecurityContext();
        
        $this->assertTrue($security->isGranted(PlatformRoles::ADMIN));
        $this->assertTrue($security->isGranted(array('ROLE_FOO', 'ROLE_BAR')));
        $this->assertTrue($security->isGranted('VIEW', new \stdClass()));
        $this->assertTrue($security->isGranted('VIEW', ClassIdentity::fromDomainClass(__CLASS__)));
    }
}