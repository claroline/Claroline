<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadWorkspaceData;
use Claroline\CoreBundle\Entity\WorkspaceRole;

class AbstractWorkspaceTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('ws_creator'));
        $this->loadWorkspaceFixture(array('ws_a', 'ws_b'));
        $this->markTestSkipped("RoleWorkspace entity removed");
    }

    public function testInitBaseRolesRequireWorkspaceToHaveAnIdentifier()
    {
        $this->setExpectedException('RuntimeException');

        $ws = new SimpleWorkspace();
        $ws->initBaseRoles();
    }

    public function testAddCustomRoleRequireWorkspaceToHaveAnIdentifier()
    {
        $this->setExpectedException('RuntimeException');

        $ws = new SimpleWorkspace();
        $ws->addCustomRole(new WorkspaceRole);
    }

    public function testAddCustomRoleDoesntAcceptRolesAlreadyBoundToAnotherWorkspace()
    {
        $this->setExpectedException('RuntimeException');

        $wsA = $this->getFixtureReference('workspace/ws_a');
        $wsB = $this->getFixtureReference('workspace/ws_b');

        $wsRole = new WorkspaceRole();
        $wsRole->setWorkspace($wsA);
        $wsRole->setName('FOO');

        $wsB->addCustomRole($wsRole);
    }

    public function testAddCustomRoleRequiresRoleToHaveAName()
    {
        $this->setExpectedException('RuntimeException');

        $wsA = $this->getFixtureReference('workspace/ws_a');

        $customRole = new WorkspaceRole();
        $customRole->setWorkspace($wsA);

        $wsA->addCustomRole($customRole);
    }

    public function testAddThenRemoveCustomRoleDoesntAffectBaseRoles()
    {
        $wsA = $this->getFixtureReference('workspace/ws_a');
        //$wsA->initBaseRoles();

        $customRole = new WorkspaceRole();
        $customRole->setWorkspace($wsA);
        $customRole->setName('FOO');

        $wsA->addCustomRole($customRole);

        $this->assertEquals(1, count($wsA->getCustomRoles()));

        $wsA->removeCustomRole($customRole);

        $this->assertEquals(0, count($wsA->getCustomRoles()));
        $this->assertFalse(null === $wsA->getVisitorRole());
        $this->assertFalse(null === $wsA->getCollaboratorRole());
        $this->assertFalse(null === $wsA->getManagerRole());
    }

    public function testIsBaseAndIsCustomRoleMethodsReturnExpectedValues()
    {
        $wsA = $this->getFixtureReference('workspace/ws_a');
        //$wsA->initBaseRoles();

        $customRole = new WorkspaceRole();
        $customRole->setName('FOO');
        $dummyRole = new WorkspaceRole();
        $dummyRole->setName('BAR');

        $wsA->addCustomRole($customRole);

        $this->assertTrue(AbstractWorkspace::isBaseRole($wsA->getVisitorRole()->getName()));
        $this->assertTrue(AbstractWorkspace::isBaseRole($wsA->getCollaboratorRole()->getName()));
        $this->assertTrue(AbstractWorkspace::isBaseRole($wsA->getManagerRole()->getName()));
        $this->assertFalse(AbstractWorkspace::isBaseRole($customRole->getName()));
        $this->assertFalse(AbstractWorkspace::isBaseRole($dummyRole->getName()));
        $this->assertFalse(AbstractWorkspace::isCustomRole($wsA->getVisitorRole()->getName()));
        $this->assertFalse(AbstractWorkspace::isCustomRole($wsA->getCollaboratorRole()->getName()));
        $this->assertFalse(AbstractWorkspace::isCustomRole($wsA->getManagerRole()->getName()));
        $this->assertTrue(AbstractWorkspace::isCustomRole($customRole->getName()));
        $this->assertFalse(AbstractWorkspace::isCustomRole($dummyRole->getName()));
    }
}