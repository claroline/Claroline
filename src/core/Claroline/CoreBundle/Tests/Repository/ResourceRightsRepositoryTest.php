<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class ResourceRightsRepositoryTest extends FixtureTestCase
{
    public function testFindNonAdminRightsReturnsRightsForAnonymousAndWorkspaceRoles()
    {
        $this->loadPlatformRoleData();
        $this->loadUserData(array('john' => 'user'));
        $this->loadDirectoryData('john', array('john/dir1'));

        // this method doesnt return admin/anonymous roles
        $workspaceRoles = $this->em->getRepository('Claroline\CoreBundle\Entity\Role')
            ->getWorkspaceRoles($this->getWorkspace('john'));
        // this method shouldn't return admin roles
        $dirRights = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceRights')
            ->findNonAdminRights($this->getDirectory('dir1'));
        $this->assertEquals(count($dirRights) - 1, count($workspaceRoles));
        $rightRoles = array();

        foreach ($dirRights as $right) {
            $rightRoles[] = $right->getRole()->getName();
        }

        $this->assertContains('ROLE_ANONYMOUS', $rightRoles);

        foreach ($workspaceRoles as $role) {
            $this->assertContains($role->getName(), $rightRoles);
        }
    }
}