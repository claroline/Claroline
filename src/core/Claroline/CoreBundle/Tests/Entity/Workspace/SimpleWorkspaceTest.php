<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class SimpleWorkspaceTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('admin', 'ws_creator'));
        $this->loadWorkspaceFixture(array('ws_d', 'ws_f'));
    }

    public function testPublicWorkspaceCannotBeASubWorkspaceOfAPrivateWorkspace()
    {
        $this->setExpectedException('RuntimeException');

        $wsD = $this->getFixtureReference('workspace/ws_d');

        $wsX = new SimpleWorkspace();
        $wsX->setName('Workspace X');
        $wsX->setParent($wsD);
    }

    public function testSubWorkspaceOfAPrivateWorkspaceCannotBeMadePublic()
    {
        $this->loadWorkspaceFixture(array('ws_e'));
        $wsF = $this->getFixtureReference('workspace/ws_f');
        $wsE = $this->getFixtureReference('workspace/ws_e');
        $wsE->setPublic(true);
        $wsF->setParent($wsE);
        $wsE->setPublic(false);
        $this->setExpectedException('RuntimeException');

        $wsF->setPublic(true);
    }
}