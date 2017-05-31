<?php

namespace Claroline\CoreBundle\Tests\API\Organization;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class ResourceNodeControllerTest extends TransactionalTestCase
{
    /** @var Persister */
    private $persister;
    /** @var User */
    private $john;
    /** @var User */
    private $admin;

    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
        $this->john = $this->persister->user('john');
        $roleAdmin = $this->persister->role('ROLE_ADMIN');
        $this->admin = $this->persister->user('admin');
        $this->admin->addRole($roleAdmin);
        $this->persister->persist($this->admin);
        $this->persister->flush();
    }

    public function testGetResourceNodeAction()
    {
        $this->login($this->john);
        $resource = $this->persister->file('test', 'text/html', true, $this->john);
        $this->client->request('GET', "/api/resources/{$resource->getResourceNode()->getGuid()}");
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
}
