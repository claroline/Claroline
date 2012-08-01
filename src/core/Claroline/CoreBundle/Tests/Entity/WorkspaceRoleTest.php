<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class WorkspaceRoleTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadWorkspaceFixture();
    }

    public function testAWorkspaceRoleCannotBeReallocatedToAnotherWorkspace()
    {
        $this->setExpectedException('RuntimeException');

        $wsA = $this->getFixtureReference('workspace/ws_a');
        $wsB = $this->getFixtureReference('workspace/ws_a');

        $wsRole = new WorkspaceRole();
        $wsRole->setName('ROLE_FOO');
        $wsRole->setWorkspace($wsA);

        $wsRole->setWorkspace($wsB);
    }
}