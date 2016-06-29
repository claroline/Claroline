<?php

namespace Claroline\CoreBundle\Tests\API\Organization;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

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
        $form = array('organization_form' => array(
            'name' => 'orga',
        ));
        $this->client->request('POST', '/api/organizations.json', $form);
        $data = $this->client->getResponse()->getContent();
        //let's check now
        $this->client->request('GET', '/api/organizations.json');
        $data = $this->client->getResponse()->getContent();
        //there is a default organization
        $this->assertEquals(2, count(json_decode($data, true)));
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

    //@route: api_delete_organization
    //@url: /api/organizations/{organization}.{_format}
    //@method: DELETE
    public function testDeleteOrganizationAction()
    {
        $this->orga = $this->persister->organization('orga1');
        $this->persister->flush();
        $this->logIn($this->admin);
        $this->client->request('DELETE', "/api/organizations/{$this->orga->getId()}.json");

        //let's check now
        $this->client->request('GET', '/api/organizations.json');
        $data = $this->client->getResponse()->getContent();
        //there is a default organization
        $this->assertEquals(1, count(json_decode($data, true)));
    }

    //@route: api_delete_organization
    //@url: /api/organizations/{organization}.{_format}
    //@method: DELETE
    public function testDeleteOrganizationActionIsProtected()
    {
        $this->orga = $this->persister->organization('orga1');
        $this->persister->flush();
        $this->logIn($this->john);
        $this->client->request('DELETE', "/api/organizations/{$this->orga->getId()}.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_organizations
    //@url: /api/organizations.{_format}
    public function testGetOrganizationsAction()
    {
        $this->persister->organization('orga1');
        $this->persister->organization('orga2');
        $this->persister->flush();
        $this->logIn($this->admin);
        $this->client->request('GET', '/api/organizations.json');
        $data = $this->client->getResponse()->getContent();
        //there is a default organization
        $this->assertEquals(3, count(json_decode($data, true)));
    }

    //@route: api_get_organizations
    //@url: /api/organizations.{_format}
    public function testGetOrganizationsActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('GET', '/api/organizations.json');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_organization_list
    //@url: /api/organization/list.{_format}
    public function testGetOrganizationListAction()
    {
        $this->persister->organization('orga1');
        $this->persister->organization('orga2');
        $this->persister->flush();
        $this->logIn($this->admin);
        $this->client->request('GET', '/api/organization/list.json');
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(3, count(json_decode($data, true)));
    }

    //@route: api_get_organization_list
    //@url: /api/organization/list.{_format}
    public function testGetOrganizationListActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('GET', '/api/organization/list.json');
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
        $data = $this->client->getResponse()->getContent();
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
        $fields = array(
            'name' => 'rename',
            'email' => 'toto@toto.net',
            'administrators' => $this->admin->getId(),
            'locations' => array($here->getId()),
        );
        $form = array('organization_form' => $fields);

        $this->client->request('PUT', "/api/organizations/{$orga->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_put_organization
    //@url: /api/organizations/{organization}.{_format}
    public function testPutOrganizationActionIsProtected()
    {
        $orga = $this->persister->organization('orga');
        $this->persister->flush();
        $this->logIn($this->john);

        $fields = array(
            'name' => 'rename',
            'email' => 'toto@toto.net',
            'administrators' => $this->admin->getId(),
        );
        $form = array('organization_form' => $fields);

        $this->client->request('PUT', "/api/organizations/{$orga->getId()}.json", $form);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}
