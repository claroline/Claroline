<?php

namespace Claroline\CoreBubdle\Tests\API\Workspace;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

class WorkspaceControllerTest extends TransactionalTestCase
{
    /** @var Persister */
    private $persister;
    /** @var User */
    private $admin;

    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    public function testGetUserWorkspacesAction()
    {
        //initialization
        $admin = $this->createAdmin();
        $this->persister->workspace('ws1', $admin);
        $this->persister->workspace('ws2', $admin);
        $this->persister->flush();

        //tests
        $this->logIn($admin);
        $this->client->request('GET', "/api/user/{$admin->getId()}/workspaces");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(2, count($data));
        $this->assertEquals('ws1', $data[0]['name']);
        $this->assertEquals('ws2', $data[1]['name']);
    }

    public function testPostWorkspaceUserAction()
    {
        $admin = $this->createAdmin();
        $this->logIn($admin);

        $values = array(
            'name' => 'workspace',
            'code' => 'workspace',
            'maxStorageSize' => '10MB',
            'maxUploadResources' => '50',
            'maxUsers' => '50',
        );

        $data['workspace_form'] = $values;
        $this->client->request('POST', "/api/workspace/user/{$admin->getId()}", $data);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data['name'], 'workspace');
    }

    public function testPutWorkspaceUserAction()
    {
        $admin = $this->createAdmin();
        $workspace = $this->persister->workspace('workspace', $admin);
        $this->logIn($admin);

        $values = array(
            'name' => 'new',
            'code' => 'workspace',
            'maxStorageSize' => '10MB',
            'maxUploadResources' => '50',
            'maxUsers' => '50',
        );

        $data['workspace_form'] = $values;
        $this->client->request('PUT', "/api/workspace/{$workspace->getId()}}", $data);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data['name'], 'new');
    }

    private function createAdmin()
    {
        $admin = $this->persister->user('admin');
        $this->persister->grantAdminRole($admin);
        $this->persister->flush();

        return $admin;
    }
}
