<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class ResourceVoterTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
    }
    
    public function testNonWorkspaceMemberCannotAccessWorkspaceResources()
    {
        $em = $this->getEntityManager();
        $manager = $this->getFixtureReference('user/ws_creator');
        $user = $this->getFixtureReference('user/user');

        $rootResource = $em
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->getRootForWorkspace($manager->getPersonalWorkspace());
        $this->logUser($user);

        $this->assertFalse($this->getSecurityContext()->isGranted('VIEW', $rootResource));
    }

    public function testWorkspaceMemberCanAccessWorkspaceResources()
    {
        $em = $this->getEntityManager();
        $manager = $this->getFixtureReference('user/ws_creator');
        $user = $this->getFixtureReference('user/user');
        $user->addRole($manager->getPersonalWorkspace()->getVisitorRole());
        $em->persist($user);
        $em->flush();

        $rootResource = $em
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->getRootForWorkspace($manager->getPersonalWorkspace());
        $this->logUser($user);

        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $rootResource));
    }
}