<?php

namespace Claroline\CoreBundle\Tests\API\Organization;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

/**
 * Specific tests for organizations
 * How to run:
 * - create database
 * - php app/console claroline:init_test_schema --env=test
 * - php app/console doctrine:schema:update --force --env=test
 * - bin/phpunit vendor/claroline/core-bundle/Tests/API/Organization/Location/ControllerTest.php -c app/phpunit.xml
 */
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
        $this->john = $this->persister->user('john');
        $roleAdmin = $this->persister->role('ROLE_ADMIN');
        $this->admin = $this->persister->user('admin');
        $this->admin->addRole($roleAdmin);
        $this->here = $this->persister->location('here');
        $this->nowhere = $this->persister->location('nowhere');
        $this->persister->persist($this->admin);
        $this->persister->flush();
    }

    //@route: api_get_locations
    //@url: /api/locations.{_format} 
    public function testGetLocationsAction()
    {
        $this->logIn($this->admin);
        $this->client->request('GET', "/api/locations.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(2, count($data));
    }

    //@route: api_get_locations
    //@url: /api/locations.{_format} 
    public function testGetLocationsActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('GET', "/api/locations.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }


    //@route: api_get_create_location_form
    //@url: /api/create/location/form.{_format}
    public function testGetCreateLocationFormAction()
    {
        $this->logIn($this->admin);
        $this->client->request('GET', '/api/create/location/form.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_create_location_form
    //@url: /api/create/location/form.{_format}
    public function testGetCreateLocationFormActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('GET', '/api/create/location/form.json');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_edit_location_form
    //@url: /api/edits/{location}/location/form.{_format}
    public function testGetEditLocationFormAction()
    {
        $this->logIn($this->admin);
        $this->client->request('GET', "/api/edits/{$this->here->getId()}/location/form.json");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_edit_location_form
    //@url: /api/edits/{location}/location/form.{_format}
    public function testGetEditLocationFormActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('GET', "/api/edits/{$this->here->getId()}/location/form.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_post_location
    //@url: /api/locations.{_format} 
    //@method: POST
    public function testPostLocationAction()
    {
        $this->logIn($this->admin);
        $fields = array(
            'name' => 'potterStreet',
            'boxNumber' => 'potterStreet',
            'streetNumber' => 'potterStreet',
            'street' => 'potterStreet',
            'pc' => 'potterStreet',
            'town' => 'potterStreet',
            'country' => 'potterStreet',
            'phone' => 'potterStreet'
        );
        $form = array('location_form' => $fields);
        $this->client->request('POST', 'api/locations.json', $form);

        //let's check now
        $this->client->request('GET', "/api/locations.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(3, count($data));
    }

    //@route: api_post_location
    //@url: /api/locations.{_format} 
    //@method: POST
    public function testPostLocationActionIsProtected()
    {
        $this->logIn($this->john);
        $fields = array(
            'name' => 'potterStreet',
            'boxNumber' => 'potterStreet',
            'streetNumber' => 'potterStreet',
            'street' => 'potterStreet',
            'pc' => 'potterStreet',
            'town' => 'potterStreet',
            'country' => 'potterStreet',
            'phone' => 'potterStreet'
        );
        $form = array('location_form' => $fields);
        $this->client->request('POST', 'api/locations.json', $form);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_put_location
    //@url: /api/locations/{location}.{_format} 
    //@method: PUT
    public function testPutLocationAction()
    {
        $this->logIn($this->admin);
        $fields = array(
            'name' => 'potterStreet',
            'boxNumber' => 'potterStreet',
            'streetNumber' => 'potterStreet',
            'street' => 'potterStreet',
            'pc' => 'potterStreet',
            'town' => 'potterStreet',
            'country' => 'potterStreet',
            'phone' => 'potterStreet'
        );
        $form = array('location_form' => $fields);
        $this->client->request('PUT', "api/locations/{$this->here->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('potterStreet', $data['name']);

    }

    //@route: api_put_location
    //@url: /api/locations/{location}.{_format} 
    //@method: PUT
    public function testPutLocationActionIsProtected()
    {
        $this->logIn($this->john);
        $fields = array(
            'name' => 'potterStreet',
            'boxNumber' => 'potterStreet',
            'streetNumber' => 'potterStreet',
            'street' => 'potterStreet',
            'pc' => 'potterStreet',
            'town' => 'potterStreet',
            'country' => 'potterStreet',
            'phone' => 'potterStreet'
        );
        $form = array('location_form' => $fields);
        $this->client->request('PUT', "api/locations/{$this->here->getId()}.json", $form);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_delete_location
    //@url: /api/locations/{location}.{_format} 
    //@method: DELETE
    public function testDeleteLocationAction()
    {
        $this->logIn($this->admin);
        $this->client->request('DELETE', "/api/locations/{$this->here->getId()}.json");

        //let's check now
        $this->client->request('GET', "/api/locations.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(1, count($data));
    }

    //@route: api_delete_location
    //@url: /api/locations/{location}.{_format} 
    //@method: DELETE
    public function testDeleteLocationActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('DELETE', "/api/locations/{$this->here->getId()}.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}