<?php

namespace Claroline\CoreBundle\Tests\API\Organization;

use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class LocationControllerTest extends TransactionalTestCase
{
    /** @var Persister */
    private $persister;
    /** @var User */
    private $john;
    /** @var User */
    private $admin;
    /** @var Location */
    private $where;
    /** @var Location */
    private $nowhere;

    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    //@route: api_get_locations
    //@url: /api/locations.{_format}
    public function testGetLocationsAction()
    {
        $admin = $this->createAdmin();
        $this->persister->location('here');
        $this->persister->location('nowhere');
        $this->persister->flush();

        $this->logIn($admin);
        $this->client->request('GET', '/api/locations.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(2, count($data));
    }

    //@route: api_get_locations
    //@url: /api/locations.{_format}
    public function testGetLocationsActionIsProtected()
    {
        $john = $this->persister->user('john');
        $this->persister->flush();

        $this->logIn($john);
        $this->client->request('GET', '/api/locations.json');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_location_create_form
    //@url: /api/location/create/form.{_format}
    public function testGetCreateLocationFormAction()
    {
        $admin = $this->createAdmin();
        $this->persister->flush();

        $this->logIn($admin);
        $this->client->request('GET', '/api/location/create/form.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_location_create_form
    //@url: /api/location/create/form.{_format}
    public function testGetCreateLocationFormActionIsProtected()
    {
        $john = $this->persister->user('john');
        $this->persister->flush();

        $this->logIn($john);
        $this->client->request('GET', '/api/location/create/form.json');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_location_edit_form
    //@url: /api/edits/{location}/location/form.{_format}
    public function testGetEditLocationFormAction()
    {
        $admin = $this->createAdmin();
        $here = $this->persister->location('here');
        $this->persister->flush();

        $this->logIn($admin);
        $this->client->request('GET', "/api/location/{$here->getId()}/edit/form.json");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_location_edit_form
    //@url: /api/edits/{location}/location/form.{_format}
    public function testGetEditLocationFormActionIsProtected()
    {
        $john = $this->persister->user('john');
        $here = $this->persister->location('here');
        $this->persister->flush();

        $this->logIn($john);
        $this->client->request('GET', "/api/location/{$here->getId()}/edit/form.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_post_location
    //@url: /api/locations.{_format}
    //@method: POST
    public function testPostLocationAction()
    {
        $admin = $this->createAdmin();
        $this->persister->flush();

        $this->logIn($admin);
        $fields = [
            'name' => 'potterStreet',
            'boxNumber' => 'potterStreet',
            'streetNumber' => 'potterStreet',
            'street' => 'potterStreet',
            'pc' => 'potterStreet',
            'town' => 'potterStreet',
            'country' => 'potterStreet',
            'phone' => 'potterStreet',
        ];
        $form = ['location_form' => $fields];
        $this->client->request('POST', 'api/locations.json', $form);

        //let's check now
        $this->client->request('GET', '/api/locations.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($data));
    }

    //@route: api_post_location
    //@url: /api/locations.{_format}
    //@method: POST
    public function testPostLocationActionIsProtected()
    {
        $john = $this->persister->user('john');
        $this->persister->flush();

        $this->logIn($john);
        $fields = [
            'name' => 'potterStreet',
            'boxNumber' => 'potterStreet',
            'streetNumber' => 'potterStreet',
            'street' => 'potterStreet',
            'pc' => 'potterStreet',
            'town' => 'potterStreet',
            'country' => 'potterStreet',
            'phone' => 'potterStreet',
        ];
        $form = ['location_form' => $fields];
        $this->client->request('POST', 'api/locations.json', $form);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_put_location
    //@url: /api/locations/{location}.{_format}
    //@method: PUT
    public function testPutLocationAction()
    {
        $admin = $this->createAdmin();
        $here = $this->persister->location('here');
        $this->persister->flush();

        $this->logIn($admin);
        $fields = [
            'name' => 'potterStreet',
            'boxNumber' => 'potterStreet',
            'streetNumber' => 'potterStreet',
            'street' => 'potterStreet',
            'pc' => 'potterStreet',
            'town' => 'potterStreet',
            'country' => 'potterStreet',
            'phone' => 'potterStreet',
        ];
        $form = ['location_form' => $fields];
        $this->client->request('PUT', "api/locations/{$here->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('potterStreet', $data['name']);
    }

    //@route: api_put_location
    //@url: /api/locations/{location}.{_format}
    //@method: PUT
    public function testPutLocationActionIsProtected()
    {
        $john = $this->persister->user('john');
        $here = $this->persister->location('here');
        $this->persister->flush();

        $this->logIn($john);
        $fields = [
            'name' => 'potterStreet',
            'boxNumber' => 'potterStreet',
            'streetNumber' => 'potterStreet',
            'street' => 'potterStreet',
            'pc' => 'potterStreet',
            'town' => 'potterStreet',
            'country' => 'potterStreet',
            'phone' => 'potterStreet',
        ];
        $form = ['location_form' => $fields];
        $this->client->request('PUT', "api/locations/{$here->getId()}.json", $form);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_delete_location
    //@url: /api/locations/{location}.{_format}
    //@method: DELETE
    public function testDeleteLocationAction()
    {
        $admin = $this->createAdmin();
        $here = $this->persister->location('here');
        $this->persister->flush();

        $this->logIn($admin);
        $this->client->request('DELETE', "/api/locations/{$here->getId()}.json");

        //let's check now
        $this->client->request('GET', '/api/locations.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, count($data));
    }

    //@route: api_delete_location
    //@url: /api/locations/{location}.{_format}
    //@method: DELETE
    public function testDeleteLocationActionIsProtected()
    {
        $john = $this->persister->user('john');
        $here = $this->persister->location('here');
        $this->persister->flush();

        $this->logIn($john);
        $this->client->request('DELETE', "/api/locations/{$here->getId()}.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    private function createAdmin()
    {
        $admin = $this->persister->user('admin');
        $roleAdmin = $this->persister->role('ROLE_ADMIN');
        $admin->addRole($roleAdmin);
        $this->persister->persist($admin);

        return $admin;
    }
}
