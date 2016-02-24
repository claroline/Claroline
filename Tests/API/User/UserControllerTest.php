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
    /** @var Role*/
    private $teacherRole;
    /** @var Role*/ 
    private $baseRole;

    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
        $this->john = $this->persister->user('john');
        $roleAdmin = $this->persister->role('ROLE_ADMIN');
        $this->teacherRole = $this->persister->role('ROLE_TEACHER');
        $this->baseRole = $this->persister->role('ROLE_BASE');
        $this->admin = $this->persister->user('admin');
        $this->admin->addRole($roleAdmin);
        $organization = $this->persister->organization('organization');
        $this->adminOrga = $this->persister->user('adminOrga');
        $this->userOrga = $this->persister->user('userOrga');
        $this->userOrga->addRole($this->baseRole);
        $this->adminOrga->addAdministratedOrganization($organization);
        $this->userOrga->addOrganization($organization);
        $this->persister->persist($this->userOrga);
        $this->persister->persist($this->adminOrga);
        $this->persister->persist($this->admin);
        $this->persister->flush();
    }

    private function initGroup()
    {
        $this->group = $this->persister->group('group');
        //Do more stuff here. Yolo.
    }
/*
    //@url: /api/users.{_format}  
    //@route: api_get_users
    public function testGetUsersAction()
    {
        $this->logIn($this->admin);
        $this->client->request('GET', '/api/users.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(4, count(json_decode($data, true)));
    }

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
        $this->client->request('GET', $url . '?username[]=john');
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

        $fields = array(
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
        $form = array('profile_form_creation' => $fields);
        $this->client->request('POST', '/api/users.json', $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('toto', $data['username']);
    }

    //check we can only add we manage
    //@url: /api/users.{_format} 
    //@route: api_post_user
    public function testPostUserActionIsProtected()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    //@url: /api/users.{_format} 
    //@route: api_put_user
    public function testPutUserAction()
    {
        $this->logIn($this->adminOrga);
        $fields = array(
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net'
        );
        $form = array('profile_form' => $fields);
        $this->client->request('PUT', "/api/users/{$this->userOrga->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('toto', $data['username']);
    }

    //@url: /api/users.{_format} 
    //@route: api_put_user
    public function testPutUserActionIsProtected()
    {
        $this->logIn($this->adminOrga);
        $fields = array(
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net'
        );
        $form = array('profile_form' => $fields);
        $this->client->request('PUT', "/api/users/{$this->userOrga->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);

        $this->logIn($this->john);
        $fields = array(
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net'
        );
        $form = array('profile_form' => $fields);
        $this->client->request('PUT', "/api/users/{$this->userOrga->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_user
    //@url: /api/users/{user}.{_format} 
    public function getUserAction()
    {
        $this->logIn($this->admin);
        $this->client->request('GET', "api/users/{$this->john->getId()}.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data['username'], 'john');
    }

    //@route: api_get_user
    //@url: /api/users/{user}.{_format} 
    public function testGetUserActionIsProtected()
    {
        $this->logIn($this->adminOrga);
        $this->client->request('GET', "api/users/{$this->john->getId()}.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_delete_user
    //@url: /api/users/{user}.{_format}
    public function testDeleteUserAction()
    {
        $this->logIn($this->adminOrga);
        $this->client->request('DELETE', "/api/users/{$this->userOrga->getId()}.json");

        //count the amount of users now...
        $url = '/api/searches/0/users/10.json';
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(0, count($data['users']));
    }

    //@route: api_delete_user
    //@url: /api/users/{user}.{_format}
    public function testDeleteUserActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('DELETE', "/api/users/{$this->userOrga->getId()}.json");
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        //count the amount of users now...
        $url = '/api/searches/0/users/10.json';
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(0, count($data['users']));
    }

    //@route: api_delete_users
    //@url: /api/users.{_format}   
    public function testDeleteUsersAction()
    {
        $this->logIn($this->adminOrga);
        $this->client->request('DELETE', "/api/users.json?userIds[]={$this->userOrga->getId()}");

        //count the amount of users now...
        $url = '/api/searches/0/users/10.json';
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(0, count($data['users']));
    }

    //@route: api_delete_users
    //@url: /api/users.{_format}   
    public function testDeleteUsersActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('DELETE', "/api/users.json?userIds[]={$this->userOrga->getId()}");
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_add_user_role
    //@url: /api/users/{user}/roles/{role}/add.{_format}
    public function testAddUserRoleAction()
    {
        $preCount = count($this->userOrga->getRoles());
        $this->logIn($this->adminOrga);
        $this->client->request('PATCH', "/api/users/{$this->userOrga->getId()}/roles/{$this->teacherRole->getId()}/add.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($preCount + 1, count($data['roles']));
    }

    //@route: api_add_user_role
    //@url: /api/users/{user}/roles/{role}/add.{_format}
    public function testAddUserRoleActionIsProtected()
    {
        $this->logIn($this->userOrga);
        $this->client->request('PATCH', "/api/users/{$this->userOrga->getId()}/roles/{$this->teacherRole->getId()}/add.json");
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    
    //@route: api_remove_user_role
    //@url: /api/users/{user}/roles/{role}/remove.{_format} 
    //MAYBE CHANGE THIS TO DELETE BECAUSE THIS SHOULD NOT BE A GET
    public function testRemoveUserRoleAction()
    {
        $preCount = count($this->userOrga->getRoles());
        $this->logIn($this->adminOrga);
        $this->client->request('GET', "/api/users/{$this->userOrga->getId()}/roles/{$this->baseRole->getId()}/remove.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($preCount - 1, count($data['roles']));
    }

    //@route: api_remove_user_role
    //@url: /api/users/{user}/roles/{role}/remove.{_format} 
    public function testRemoveUserRoleActionIsProtected()
    {
        $this->logIn($this->userOrga);
        $this->client->request('GET', "/api/users/{$this->userOrga->getId()}/roles/{$this->baseRole->getId()}/remove.json");
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_add_user_group
    //@url: /api/users/{user}/groups/{group}/add.{_format} 
    public function testAddUserGroupAction()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }


    //@route: api_add_user_group
    //@url: /api/users/{user}/groups/{group}/add.{_format} 
    public function testAddUserGroupActionIsProtected()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }    


    //@route: api_remove_user_group
    //@url: /api/users/{user}/groups/{group}/remove.{_format}
    public function testRemoveUserGroupAction()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    //@route: api_remove_user_group
    //@url: /api/users/{user}/groups/{group}/remove.{_format}
    public function testRemoveUserGroupActionIsProtected()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    
    //@route: api_get_user_admin_actions
    //@url: /api/user/admin/actions.{_format} 
    public function testGetUserAdminActionsAction()
    {
        $this->logIn($this->admin);
        $this->client->request('GET', '/api/user/admin/actions.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        //this will vary depending on plugins...
        $this->assertGreaterThan(1, count($data));
    }

    */
    //@route: api_users_password_initialize
    //@url: /api/passwords/initializes/users.{_format}
    public function testUsersPasswordInitializeAction()
    {

    }

    //@route: api_users_password_initialize
    //@url: /api/passwords/initializes/users.{_format}
    public function testUsersPasswordInitializeActionIsProtected()
    {

    }
/*
    //@route: api_add_users_to_group
    //@url: /api/users/{group}/to/group/add.{_format} 
    public function testAddUsersToGroupAction()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    //@route: api_add_users_to_group
    //@url: /api/users/{group}/to/group/add.{_format} 
    public function testAddUsersToGroupActionIsProtected()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    //@route: api_remove_users_from_group
    //@url: /api/users/{group}/from/group/remove.{_format}
    public function testRemoveUsersFromGroupAction()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    //@route: api_remove_users_from_group
    //@url: /api/users/{group}/from/group/remove.{_format}
    public function testRemoveUsersFromGroupActionIsProtected()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }*/
}