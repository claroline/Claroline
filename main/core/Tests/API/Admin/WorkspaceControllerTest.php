<?php

namespace Claroline\CoreBundle\Tests\API\Admin;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

class WorkspaceControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    public function testPostWorkspaceUserAction()
    {
        $admin = $this->createAdmin();
        $this->login($admin);

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
        $this->login($admin);

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
