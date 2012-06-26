<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadWorkspaceData;

class SimpleWorkspaceTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
                $this->loadUserFixture();
        $this->loadWorkspaceFixture();
    }

    public function testPublicWorkspaceCannotBeASubWorkspaceOfAPrivateWorkspace()
    {
        $this->setExpectedException('Claroline\CoreBundle\Exception\ClarolineException');

        $wsD = $this->getFixtureReference('workspace/ws_d');

        $wsX = new SimpleWorkspace();
        $wsX->setName('Workspace X');
        $wsX->setParent($wsD);
    }

    public function testSubWorkspaceOfAPrivateWorkspaceCannotBeMadePublic()
    {
        $this->setExpectedException('Claroline\CoreBundle\Exception\ClarolineException');

        $wsF = $this->getFixtureReference('workspace/ws_f');
        $wsF->setPublic(true);
    }
}