<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Testing\FixtureTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadWorkspaceData;

class WorkspaceRoleTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixture(new LoadWorkspaceData());
    }
    
    public function testAWorkspaceRoleCannotBeReallocatedToAnotherWorkspace()
    {
        $this->setExpectedException('Claroline\CoreBundle\Exception\ClarolineException');
        
        $wsA = $this->getFixtureReference('workspace/ws_a');
        $wsB = $this->getFixtureReference('workspace/ws_a');
        
        $wsRole = new WorkspaceRole();
        $wsRole->setName('ROLE_FOO');
        $wsRole->setWorkspace($wsA);
        
        $wsRole->setWorkspace($wsB);
    }
}