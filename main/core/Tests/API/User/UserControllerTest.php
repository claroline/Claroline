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
 * - bin/phpunit vendor/claroline/core-bundle/Tests/API/User/UserControllerTest.php -c app/phpunit.xml.
 */
class UserControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    //@url: /api/users.{_format}
    //@route: api_get_users
    public function testGetUsersAction()
    {
        //initialization
        $admin = $this->createAdmin();
        $this->persister->flush();

        //tests
        $this->logIn($admin);
        $this->client->request('GET', '/api/users.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(1, count(json_decode($data, true)));
    }

    //@url: /api/users.{_format}
    //@route: api_get_users
    public function testGetUsersActionIsSecured()
    {
        //initialization
        $john = $this->persister->user('john');
        $this->persister->flush();

        //tests
        $this->logIn($john);
        $this->client->request('GET', '/api/users.json');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_search_users
    //@url: /api/users/page/{page}/limit/{limit}/search.{_format}
    public function testSearchUsersAction()
    {
        //initialization
        $john = $this->persister->user('john');
        $teacherRole = $this->persister->role('ROLE_TEACHER');
        $baseRole = $this->persister->role('ROLE_BASE');
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $userOrga = $this->createUserOrga($organization);
        $admin = $this->createAdmin();
        $this->persister->flush();

        //tests
        $url = '/api/users/page/0/limit/10/search.json';
        //ADMINISTRATOR USAGE !
        $this->logIn($admin);

        //base search should retrieve everything for the administrator
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(4, count($data['users']));

        //now we're adding a simple filter
        $this->client->request('GET', $url.'?username[]=john');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(1, count($data['users']));

        //ORGANIZATION MANAGER USAGE !
        $this->logIn($adminOrga);

        //base search should retrive all the users of my organizations
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(1, count($data['users']));

        //USER USAGE
        $this->logIn($john);

        //base search should retrive all the users of my organizations. If I don't have any, it should be 0
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(0, count($data['users']));
    }

    //@url: /api/user/searchable/fields.{_format}
    //@route: api_get_user_fields
    public function testGetUsersSearchableFieldsAction()
    {
        //initialization
        $john = $this->persister->user('john');
        $this->persister->flush();
        $this->logIn($john);

        //tests
        $this->client->request('GET', '/api/users/fields.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(7, count($data));
    }

    //@url: /api/users.{_format}
    //@route: api_post_user
    public function testPostUserAction()
    {
        //initialization
        $admin = $this->createAdmin();
        $this->persister->flush();
        $this->setPlatformOption('self_registration', true);

        //tests
        $this->logIn($admin);
        $fields = array(
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'plainPassword' => array(
                    'first' => 'toto',
                    'second' => 'toto',
                ),
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net',
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
        //initialization
        $john = $this->persister->user('john');
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $userOrga = $this->createUserOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $fields = array(
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net',
        );
        $form = array('profile_form' => $fields);
        $this->client->request('PUT', "/api/users/{$userOrga->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('toto', $data['username']);
    }

    //@url: /api/users.{_format}
    //@route: api_put_user
    public function testPutUserActionIsProtected()
    {
        //initialization
        $john = $this->persister->user('john');
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $userOrga = $this->createUserOrga($organization);
        $this->persister->flush();
        $this->logIn($adminOrga);

        //tests
        $fields = array(
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net',
        );
        $form = array('profile_form' => $fields);
        $this->client->request('PUT', "/api/users/{$userOrga->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);

        $this->logIn($john);
        $fields = array(
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net',
        );
        $form = array('profile_form' => $fields);
        $this->client->request('PUT', "/api/users/{$userOrga->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_user
    //@url: /api/users/{user}.{_format}
    public function getUserAction()
    {
        //initialization
        $admin = $this->createAdmin();
        $this->persister->flush();

        //tests
        $this->logIn($this->admin);
        $this->client->request('GET', "api/users/{$admin->getId()}.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data['username'], 'john');
    }

    //@route: api_get_user
    //@url: /api/users/{user}.{_format}
    public function testGetUserActionIsProtected()
    {
        //initialization
        $john = $this->persister->user('john');
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('GET', "api/user/{$john->getId()}.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_delete_user
    //@url: /api/users/{user}.{_format}
    public function testDeleteUserAction()
    {
        //initialization
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $userOrga = $this->createUserOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('DELETE', "/api/users/{$userOrga->getId()}.json");
        //count the amount of users now...
        $url = '/api/users/page/0/limit/10/search';
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(0, count($data['users']));
    }

    //@route: api_delete_user
    //@url: /api/users/{user}.{_format}
    public function testDeleteUserActionIsProtected()
    {
        //initialization
        $john = $this->persister->user('john');
        $organization = $this->persister->organization('organization');
        $userOrga = $this->createUserOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($john);
        $this->client->request('DELETE', "/api/users/{$userOrga->getId()}.json");
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        //count the amount of users now...
        $url = '/api/users/page/0/limit/10/search';
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(0, count($data['users']));
    }

    //@route: api_delete_users
    //@url: /api/users.{_format}
    public function testDeleteUsersAction()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $userOrga = $this->createUserOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('DELETE', "/api/users.json?userIds[]={$userOrga->getId()}");
        //count the amount of users now...
        $url = '/api/users/page/0/limit/10/search';
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(0, count($data['users']));
    }

    //@route: api_delete_users
    //@url: /api/users.{_format}
    public function testDeleteUsersActionIsProtected()
    {
        //initialization
        $john = $this->persister->user('john');
        $organization = $this->persister->organization('organization');
        $userOrga = $this->createUserOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($john);
        $this->client->request('DELETE', "/api/users.json?userIds[]={$userOrga->getId()}");
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_add_user_role
    //@url: /api/users/{user}/roles/{role}/add.{_format}
    public function testAddUserRoleAction()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $userOrga = $this->createUserOrga($organization);
        $teacherRole = $this->persister->role('ROLE_TEACHER');
        $adminOrga = $this->createAdminOrga($organization);
        $this->persister->flush();

        //tests
        $preCount = count($userOrga->getRoles());
        $this->logIn($adminOrga);
        $this->client->request('PATCH', "/api/users/{$userOrga->getId()}/roles/{$teacherRole->getId()}/add.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($preCount + 1, count($data['roles']));
    }

    //@route: api_add_user_role
    //@url: /api/users/{user}/roles/{role}/add.{_format}
    public function testAddUserRoleActionIsProtected()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $userOrga = $this->createUserOrga($organization);
        $teacherRole = $this->persister->role('ROLE_TEACHER');
        $this->persister->flush();

        //tests
        $this->logIn($userOrga);
        $this->client->request('PATCH', "/api/users/{$userOrga->getId()}/roles/{$teacherRole->getId()}/add.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_remove_user_role
    //@url: /api/users/{user}/roles/{role}/remove.{_format}
    //MAYBE CHANGE THIS TO DELETE BECAUSE THIS SHOULD NOT BE A GET
    public function testRemoveUserRoleAction()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $userOrga = $this->createUserOrga($organization);
        $baseRole = $this->persister->role('ROLE_BASE');
        $userOrga->addRole($baseRole);
        $adminOrga = $this->createAdminOrga($organization);
        $this->persister->flush();

        //test
        $this->client->request('GET', "/api/users/{$userOrga->getId()}/roles/{$baseRole->getId()}/remove.json");
        $preCount = count($userOrga->getRoles());
        $this->logIn($adminOrga);
        $this->client->request('GET', "/api/users/{$userOrga->getId()}/roles/{$baseRole->getId()}/remove.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($preCount - 1, count($data['roles']));
    }

    //@route: api_remove_user_role
    //@url: /api/users/{user}/roles/{role}/remove.{_format}
    public function testRemoveUserRoleActionIsProtected()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $userOrga = $this->createUserOrga($organization);
        $baseRole = $this->persister->role('ROLE_BASE');
        $userOrga->addRole($baseRole);
        $this->persister->flush();

        //test
        $this->logIn($userOrga);
        $this->client->request('GET', "/api/users/{$userOrga->getId()}/roles/{$baseRole->getId()}/remove.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_add_user_group
    //@url: /api/users/{user}/groups/{group}/add.{_format}
    public function testAddUserGroupAction()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    //@route: api_add_user_group
    //@url: /api/users/{user}/groups/{group}/add.{_format}
    public function testAddUserGroupActionIsProtected()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    //@route: api_remove_user_group
    //@url: /api/users/{user}/groups/{group}/remove.{_format}
    public function testRemoveUserGroupAction()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    //@route: api_remove_user_group
    //@url: /api/users/{user}/groups/{group}/remove.{_format}
    public function testRemoveUserGroupActionIsProtected()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    //@route: api_get_user_admin_actions
    //@url: /api/user/admin/action.{_format}
    public function testGetUserAdminActionsAction()
    {
        //initialization
        $admin = $this->createAdmin();
        $this->persister->flush();

        //test
        $this->logIn($admin);
        $this->client->request('GET', '/api/user/admin/action.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        //this will vary depending on plugins...
        $this->assertGreaterThan(1, count($data));
    }

    //@route: api_users_password_initialize
    //@url: /api/passwords/initializes/users.{_format}
    public function testUsersPasswordInitializeAction()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $userOrga = $this->createUserOrga($organization);
        $baseRole = $this->persister->role('ROLE_BASE');
        $userOrga->addRole($baseRole);
        $adminOrga = $this->createAdminOrga($organization);
        $this->persister->flush();

        //test
        $this->login($adminOrga);
        $this->client->request('GET', "/api/passwords/initializes/users.json?userIds[]={$userOrga->getId()}");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data[0], 'success');
    }

    //@route: api_users_password_initialize
    //@url: /api/passwords/initializes/users.{_format}
    public function testUsersPasswordInitializeActionIsProtected()
    {
        $john = $this->persister->user('john');
        $jane = $this->persister->user('jane');

        $this->login($john);
        $this->client->request('GET', "/api/passwords/initializes/users.json?userIds[]={$jane->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

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
    }

    private function createAdmin()
    {
        $admin = $this->persister->user('admin');
        $roleAdmin = $this->persister->role('ROLE_ADMIN');
        $admin->addRole($roleAdmin);
        $this->persister->persist($admin);

        return $admin;
    }

    private function createAdminOrga($organization)
    {
        $adminOrga = $this->persister->user('adminOrga');
        $adminOrga->addAdministratedOrganization($organization);
        $this->persister->persist($adminOrga);

        return $adminOrga;
    }

    private function createUserOrga($organization)
    {
        $userOrga = $this->persister->user('userOrga');
        $userOrga->addOrganization($organization);
        $this->persister->persist($userOrga);

        return $userOrga;
    }
}
