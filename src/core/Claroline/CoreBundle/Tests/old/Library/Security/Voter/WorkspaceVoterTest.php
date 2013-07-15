<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class WorkspaceVoterTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('john' => 'user'));
    }

    public function testAnAuthenticationExceptionIsThrownIfAnonymousUserHasInsufficientPermissionsOnTool()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AuthenticationException');
        $context = $this->getSecurityContext();
        $context->setToken(new AnonymousToken('some_key', 'anon.', array('ROLE_ANONYMOUS')));
        // this could/should be tested for each workspace tool
        $this->getSecurityContext()->isGranted('parameters', $this->getWorkspace('john'));
    }
}