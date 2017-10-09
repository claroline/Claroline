<?php

namespace Claroline\CoreBundle\Tests\API\Organization;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class OrganizationControllerTest extends TransactionalTestCase
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

    //@route: api_post_organization
    //@url: /api/organizations.{_format}
    //@method: POST
    public function testPostOrganizationAction()
    {
        $this->logIn($this->admin);
        $form = ['organization_form' => [
            'name' => 'orga',
        ]];
        $this->client->request('POST', '/api/organizations.json', $form);
    }

    //@route: api_post_organization
    //@url: /api/organizations.{_format}
    //@method: POST
    public function testPostOrganizationActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('POST', '/api/organizations.json');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_edit_organization_form
    //@url: /api/organization/{organization}/edit/form.json
    public function testGetEditOrganizationFormAction()
    {
        $orga = $this->persister->organization('orga');
        $this->persister->flush();
        $this->logIn($this->admin);
        $this->client->request('GET', "/api/organization/{$orga->getId()}/edit/form.json");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_edit_organization_form
    //@url: /api/organization/{organization}/edit/form.json
    public function testGetEditOrganizationFormActionIsProtected()
    {
        $orga = $this->persister->organization('orga');
        $this->persister->flush();
        $this->logIn($this->john);
        $this->client->request('GET', "/api/organization/{$orga->getId()}/edit/form.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_put_organization
    //@url: /api/organizations/{organization}.{_format}
    public function testPutOrganizationAction()
    {
        $orga = $this->persister->organization('orga');
        $here = $this->persister->location('here');
        $this->persister->flush();
        $this->logIn($this->admin);
        $fields = [
            'name' => 'rename',
            'email' => 'toto@toto.net',
            'administrators' => $this->admin->getId(),
            'locations' => [$here->getId()],
        ];
        $form = ['organization_form' => $fields];

        $this->client->request('PUT', "/api/organizations/{$orga->getId()}.json", $form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_put_organization
    //@url: /api/organizations/{organization}.{_format}
    public function testPutOrganizationActionIsProtected()
    {
        $orga = $this->persister->organization('orga');
        $this->persister->flush();
        $this->logIn($this->john);

        $fields = [
            'name' => 'rename',
            'email' => 'toto@toto.net',
            'administrators' => $this->admin->getId(),
        ];
        $form = ['organization_form' => $fields];

        $this->client->request('PUT', "/api/organizations/{$orga->getId()}.json", $form);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}
