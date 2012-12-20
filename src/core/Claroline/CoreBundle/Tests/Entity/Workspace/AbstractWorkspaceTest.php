<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\Entity\Role;

class AbstractWorkspaceTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('ws_creator'));
        $this->loadWorkspaceFixture(array('ws_a', 'ws_b'));
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
        $ws->addCustomRole(new Role);
    }

    public function testAddCustomRoleDoesntAcceptRolesAlreadyBoundToAnotherWorkspace()
    {
        $this->setExpectedException('RuntimeException');

        $wsA = $this->getFixtureReference('workspace/ws_a');
        $wsB = $this->getFixtureReference('workspace/ws_b');

        $wsRole = new Role();
        $wsRole->setWorkspace($wsA);
        $wsRole->setName('FOO');
        $wsRole->setRoleType(Role::WS_ROLE);

        $wsB->addCustomRole($wsRole);
    }

    public function testAddCustomRoleRequiresRoleToHaveAName()
    {
        $this->setExpectedException('RuntimeException');

        $wsA = $this->getFixtureReference('workspace/ws_a');

        $customRole = new Role();
        $customRole->setWorkspace($wsA);
        $customRole->setRoleType(Role::WS_ROLE);

        $wsA->addCustomRole($customRole);
    }

    public function testAddThenRemoveCustomRoleDoesntAffectBaseRoles()
    {
        $wsA = $this->getFixtureReference('workspace/ws_a');
        //$wsA->initBaseRoles();

        $customRole = new Role();
        $customRole->setRoleType(Role::WS_ROLE);
        $customRole->setWorkspace($wsA);
        $customRole->setName('ROLE_FOO');

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

        $customRole = new Role();
        $customRole->setName('ROLE_FOO');
        $customRole->setRoleType(Role::WS_ROLE);
        $dummyRole = new Role();
        $dummyRole->setName('ROLE_BAR');
        $dummyRole->setRoleType(Role::WS_ROLE);

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