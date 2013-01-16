<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class AjaxAuthenticationListenerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadWorkspaceFixture();
        $this->client->followRedirects();
    }

    public function testAjaxAuthenticationListenerExceptionThrowsError()
    {
        $this->markTestSkipped('No firewall anymore');
        
        $this->client->request(
            'GET',
            "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0/unregistered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}
