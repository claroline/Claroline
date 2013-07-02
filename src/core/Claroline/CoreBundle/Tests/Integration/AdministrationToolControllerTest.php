<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class AdministrationControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('john' => 'user','admin' => 'admin'));
        $this->configHandler = $this->client
            ->getContainer()
            ->get('claroline.config.platform_config_handler');
        $this->client->followRedirects();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->configHandler->eraseTestConfiguration();
    }

    public function testAdminCanSeeTool()
    {
        $this->logUser($this->getUser('admin'));
        $this->client->request('GET', 'admin/tool/show');
        $status = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $status);
    }

    public function testRename()
    {
        $tool = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')->find(1);
        $id = $tool->getId();
        $this->client->request(
            'POST',
            "/admin/tool/modify/{$id}",
            array(
                'displayName' => 'bonjour'
                )
        );

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $status);
    }
}