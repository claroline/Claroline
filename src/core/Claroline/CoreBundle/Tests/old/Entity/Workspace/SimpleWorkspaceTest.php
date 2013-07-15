<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class SimpleWorkspaceTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('admin' => 'admin', 'ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(array('ws_d' => 'ws_creator', 'ws_f' => 'admin'));
    }

    public function testPublicWorkspaceCannotBeASubWorkspaceOfAPrivateWorkspace()
    {
        $this->setExpectedException('RuntimeException');

        $wsD = $this->getWorkspace('ws_d');
        $wsD->setPublic(false);
        $wsX = new SimpleWorkspace();
        $wsX->setName('Workspace X');
        $wsX->setParent($wsD);
    }

    public function testSubWorkspaceOfAPrivateWorkspaceCannotBeMadePublic()
    {
        $this->loadWorkspaceData(array('ws_e' => 'admin'));
        $wsF = $this->getWorkspace('ws_f');
        $wsE = $this->getWorkspace('ws_e');
        $wsE->setPublic(true);
        $wsF->setParent($wsE);
        $wsE->setPublic(false);
        $this->setExpectedException('RuntimeException');

        $wsF->setPublic(true);
    }
}