<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class ViewAsListenerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
    }

    public function testChangeRoles()
    {
        $wsId = $this->getWorkspace('ws_creator')->getId();
        $this->logUser($this->getUser('ws_creator'));

        //get Collaborator Role.
        $this->client->request(
            'GET',
            "/workspaces/{$wsId}/open/tool/home?view_as=ROLE_WS_COLLABORATOR_{$wsId}"
        );

        $securityContext = $this->client->getContainer()->get('security.context');
        $this->assertEquals(3, count($securityContext->getToken()->getRoles()));
        $this->assertTrue($securityContext->isGranted("ROLE_WS_COLLABORATOR_{$wsId}"));
        $this->assertFalse($securityContext->isGranted("ROLE_WS_MANAGER_{$wsId}"));
        $this->assertTrue($securityContext->isGranted("ROLE_USURPATE_WORKSPACE_ROLE"));
        $this->client->request(
            'GET',
            "/workspaces/{$wsId}/open/tool/home?view_as=exit"
        );

        $securityContext = $this->client->getContainer()->get('security.context');
        $this->assertTrue($securityContext->isGranted("ROLE_WS_MANAGER_{$wsId}"));
    }

    public function testViewAsIsProtected()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException');
        $this->loadUserData(array('user' => 'user'));
        $wsId = $this->getWorkspace('ws_creator')->getId();
        $this->logUser($this->getUser('user'));

        $this->client->request(
            'GET',
            "/workspaces/{$wsId}/open/tool/home?view_as=ROLE_WS_COLLABORATOR_{$wsId}"
        );

    }
}