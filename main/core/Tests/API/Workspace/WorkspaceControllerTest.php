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
        $this->assertEquals($data[0]['name'], '[COPY] default_workspace');
    }

    private function createAdmin()
    {
        $admin = $this->persister->user('admin');
        $this->persister->grantAdminRole($admin);
        $this->persister->flush();

        return $admin;
    }
}
