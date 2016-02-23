<?php

namespace Claroline\CoreBundle\Tests\API\User;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

/**
 * Specific tests for organizations
 * How to run:
 * - create database
 * - php app/console claroline:init_test_schema --env=test
 * - php app/console doctrine:schema:update --force --env=test
 * - bin/phpunit vendor/claroline/core-bundle/Tests/API/User/UserControllerTest.php -c app/phpunit.xml
 */
class UserControllerTest extends TransactionalTestCase
{
    /** @var Persister */
    private $persister;
    /** @var User */
    private $john;
    /** @var User */
    private $admin;
    /** @var User */
    private $adminOrga;
    /** @var User */
    private $userOrga;

    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
        $this->john = $this->persister->user('john');
        $roleAdmin = $this->persister->role('ROLE_ADMIN');
        $this->admin = $this->persister->user('admin');
        $this->admin->addRole($roleAdmin);
        $organization = $this->persister->organization('organization');
        $this->adminOrga = $this->persister->user('adminOrga');
        $this->userOrga = $this->persister->user('userOrga');
        $organization->addAdministrator($this->userOrga);
        $this->userOrga->addOrganization($organization);
        $this->persister->persist($this->userOrga);
        $this->persister->persist($organization);
        $this->persister->persist($this->admin);
        $this->persister->flush();
    }

    //@url: /api/users.{_format}  
    //@route: api_get_users
    public function testGetUsersAction()
    {
        $this->logIn($this->admin);
        $this->client->request('GET', '/api/users.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(4, count(json_decode($data, true)));

        //var_dump('log jhn');
        $this->logIn($this->john);
        $this->client->request('GET', '/api/users.json');
        $data = $this->client->getResponse()->getContent();
        //var_dump($data);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
/*
    //@url: /api/users.{_format}  
    //@route: api_get_users
    public function testGetUsersActionIsSecured()
    {
        $this->logIn($this->john);
        $this->client->request('GET', '/api/users.json');
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@url: /api/searches/{page}/users/{limit}.{_format}
    //@route: api_get_search_users
    public function testGetSearchUsersAction()
    {
        $url = '/api/searches/0/users/10.json';
        //ADMINISTRATOR USAGE !
        $this->login($this->admin);

        //base search should retrieve everything for the administrator
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(4, count($data['users']));

        //now we're adding a simple filter
        $this->client->request('GET', $url . '?name[]=john');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);

        $this->assertEquals(1, count($data['users']));

        //ORGANIZATION MANAGER USAGE !
        $this->login($this->adminOrga);

        //base search should retrive all the users of my organizations
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(1, count($data['users']));

        //USER USAGE
        $this->login($this->john);

        //base search should retrive all the users of my organizations. If I don't have any, it should be 0
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(0, count($data['users']));
    }

    //@url: /api/user/searchable/fields.{_format} 
    //@route: api_get_user_searchable_fields
    public function testGetUsersSearchableFieldsAction()
    {
        $this->logIn($this->john);
        $this->client->request('GET', '/api/user/searchable/fields.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(7, count($data));
    }

    //@url: /api/users.{_format} 
    //@route: api_post_user
    public function testPostUserAction()
    {
        $this->setPlatformOption('self_registration', true);
        $this->logIn($this->admin);

        $fields = $this->getToto();
        $form = array('profile_form_creation' => $fields);
        $this->client->request('POST', '/api/users.json', $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('toto', $data['username']);
    }

    //check we can only add we manage
    public function testPostUserActionIsProtected()
    {
        //do something rather smart here.
    }
*/
    //@url: /api/users.{_format} 
    //@route: api_put_user
    public function testPutUserAction()
    {
        $this->logIn($this->adminOrga);
        $fields = $this->getToto();
        $form = array('profile_form' => $fields);
        $this->client->request('PUT', "/api/users/{$this->userOrga->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('toto', $data['username']);
    }

/*
    public function testPutUserActionIsProtected()
    {
        //do something smart
    }

    public function getUserAction()
    {

    }

    public function testGetUserActionIsProtected()
    {

    }

    public function testDeleteUserAction()
    {

    }

    public function testDeleteUserActionIsProtected()
    {

    }

    public function testDeleteUsersAction()
    {

    }

    public function testDeleteUsersActionIsProtected()
    {

    }

    public function testAddUserRoleAction()
    {

    }

    //only user managers can add an remove roles... you can't add a role to yourself
    public function testAddUserRoleActionIsProtected()
    {

    }

    public function testRemoveUserRoleAction()
    {

    }

    //only user managers can add an remove roles... you can't add a role to yourself
    public function testRemoveUserRoleActionIsProtected()
    {

    }

    public function testAddUserGroupAction()
    {

    }

    public function testAddUserGroupActionIsProtected()
    {

    }    

    public function testRemoveUserGroupAction()
    {

    }

    public function testRemoveUserGroupActionIsProtected()
    {

    }

    public function testGetUserAdminActionsAction()
    {

    }

    public function testUsersPasswordInitializeAction()
    {

    }

    public function testUsersPasswordInitializeActionIsProtected()
    {

    }
        
    public function testAddUsersToGroupAction()
    {

    }

    public function testAddUsersToGroupActionIsProtected()
    {

    }

    public function testRemoveUsersFromGroupAction()
    {

    }

    public function testRemoveUsersFromGroupActionIsProtected()
    {

    }*/

    private function getToto()
    {
        return array(
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'plainPassword' => array(
                    'first' => 'toto',
                    'second' => 'toto'
                ),
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net'
        );
    }
}