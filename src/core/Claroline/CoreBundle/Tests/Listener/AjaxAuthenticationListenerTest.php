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
        $this->client->request(
            'POST',
            "/workspace/user/{$this->getFixtureReference('workspace/ws_a')->getId()}/1/limited-list.json",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}
