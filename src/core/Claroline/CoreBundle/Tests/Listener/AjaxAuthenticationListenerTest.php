<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class AjaxAuthenticationListenerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('john' => 'user'));
        $this->client->followRedirects();
    }

    public function testANotAllowedResponseIsReturnedOnAccessDeniedException()
    {
        $this->loadUserData(array('jane' => 'user'));
        $this->logUser($this->getUser('jane'));
        $this->client->request(
            'GET',
            "/workspaces/{$this->getWorkspace('john')->getId()}/open/tool/home",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}