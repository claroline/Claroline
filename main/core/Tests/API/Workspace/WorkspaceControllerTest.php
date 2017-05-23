<?php

namespace Claroline\CoreBundle\Tests\API\Workspace;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

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

        $values = [
            'name' => 'workspace',
            'code' => 'workspace',
            'maxStorageSize' => '10MB',
            'maxUploadResources' => '50',
            'maxUsers' => '50',
        ];

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

        $values = [
            'name' => 'new',
            'code' => 'workspace',
            'maxStorageSize' => '10MB',
            'maxUploadResources' => '50',
            'maxUsers' => '50',
        ];

        $data['workspace_form'] = $values;
        $this->client->request('PUT', "/api/workspace/{$workspace->getId()}", $data);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data['name'], 'new');
    }

    public function testGetCopy()
    {
        $admin = $this->createAdmin();
        $workspace = $this->persister->workspace('workspace', $admin);
        $parent = $this->client->getContainer()->get('claroline.manager.resource_manager')->getWorkspaceRoot($workspace);
        $this->persister->directory('dir1', $parent, $workspace, $admin);
        $this->persister->directory('dir2', $parent, $workspace, $admin);
        $this->persister->directory('dir3', $parent, $workspace, $admin);
        $this->persister->flush();
        $this->logIn($admin);

        $this->client->request('PATCH', '/api/workspaces/0/copy.json?ids[]=1');
        $data = $this->client->getResponse()->getContent();
        //at least it didn't crash
        $data = json_decode($data, true);
        $this->assertEquals($data[0]['name'], '[COPY] - default_workspace');
    }

    public function testSearchWorkspace()
    {
        $admin = $this->createAdmin();
        $this->persister->workspace('abc', $admin);
        $this->persister->workspace('def', $admin);

        $this->logIn($admin);

        $url = '/api/workspace/page/0/limit/10/search.json';
        $this->client->request('GET', $url.'?filters[name]=abc');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);

        $this->assertEquals(1, count($data['results']));
    }

    private function createAdmin()
    {
        $admin = $this->persister->user('admin');
        $this->persister->grantAdminRole($admin);
        $this->persister->flush();

        return $admin;
    }
}
