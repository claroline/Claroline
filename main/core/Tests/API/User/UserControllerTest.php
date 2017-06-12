<?php

namespace Claroline\CoreBundle\Tests\API\User;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class UserControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

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
        $this->assertEquals(2, count(json_decode($data, true)));
    }

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

    public function testSearchUsersAction()
    {
        //initialization
        $john = $this->persister->user('john');
        $this->persister->role('ROLE_TEACHER');
        $this->persister->role('ROLE_BASE');
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $this->createUserOrga($organization);
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

    public function testPostUserAction()
    {
        //initialization
        $admin = $this->createAdmin();
        $this->persister->flush();
        $this->setPlatformOption('self_registration', true);

        //tests
        $this->logIn($admin);
        $fields = [
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'plainPassword' => [
                    'first' => 'toto',
                    'second' => 'toto',
                ],
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net',
        ];
        $form = ['profile_form_creation' => $fields];
        $this->client->request('POST', '/api/users.json', $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('toto', $data['username']);
    }

    public function testPostUserActionIsProtected()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testPutUserAction()
    {
        //initialization
        $this->persister->user('john');
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $userOrga = $this->createUserOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $fields = [
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net',
        ];
        $form = ['profile_form' => $fields];
        $this->client->request('PUT', "/api/users/{$userOrga->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('toto', $data['username']);
    }

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
        $fields = [
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net',
        ];
        $form = ['profile_form' => $fields];
        $this->client->request('PUT', "/api/users/{$userOrga->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);

        $this->logIn($john);
        $fields = [
            'firstName' => 'toto',
            'lastName' => 'toto',
            'username' => 'toto',
            'administrativeCode' => 'toto',
            'mail' => 'toto@claroline.net',
        ];
        $form = ['profile_form' => $fields];
        $this->client->request('PUT', "/api/users/{$userOrga->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

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

    public function testGetUserActionIsProtected()
    {
        //initialization
        $john = $this->persister->user('john');
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('GET', "api/user/{$john->getId()}/get.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

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
        $preCount = count($userOrga->getRoles());
        $this->logIn($adminOrga);
        $this->client->request('GET', "/api/users/{$userOrga->getId()}/roles/{$baseRole->getId()}/remove.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($preCount - 1, count($data['roles']));
    }

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

    public function testAddUserGroupAction()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testAddUserGroupActionIsProtected()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testRemoveUserGroupAction()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testRemoveUserGroupActionIsProtected()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

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

    public function testUsersPasswordInitializeActionIsProtected()
    {
        $john = $this->persister->user('john');
        $jane = $this->persister->user('jane');

        $this->login($john);
        $this->client->request('GET', "/api/passwords/initializes/users.json?userIds[]={$jane->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAddUsersToGroupAction()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testAddUsersToGroupActionIsProtected()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testRemoveUsersFromGroupAction()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testRemoveUsersFromGroupActionIsProtected()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testPutRolesToUsersAction()
    {
        $users = [
            $this->persister->user('user1'),
            $this->persister->user('user2'),
            $this->persister->user('user3'),
        ];

        $roles = [
            $this->persister->role('ROLE_1'),
            $this->persister->role('ROLE_2'),
            $this->persister->role('ROLE_3'),
        ];

        $admin = $this->createAdmin();
        $this->persister->flush();
        $this->logIn($admin);

        $uString = '';

        foreach ($users as $user) {
            $uString .= "userIds[]={$user->getId()}&";
        }

        $rString = '';

        foreach ($roles as $role) {
            $rString .= "roleIds[]={$role->getId()}&";
        }

        $request = "/api/users/roles/add.json?{$uString}{$rString}";
        $this->client->request('PUT', $request);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(5, count($data[0]['roles']));
    }

    public function testCsvImportFacetsAction()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetPublicUserAction()
    {
        $admin = $this->createAdmin();
        $user = $this->persister->user('user');

        //A user can see himself
        $this->logIn($user);
        $this->client->request('GET', "/api/user/{$user->getId()}/public");
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(
            [
                'firstName' => 'user',
                'lastName' => 'user',
                'username' => 'user',
                'mail' => 'user@mail.com',
                'allowSendMail' => true,
                'allowSendMessage' => true,
                'id' => $user->getId(),
            ],
            $data
        );

        //A use can see other people...
        $this->client->request('GET', "/api/user/{$admin->getId()}/public");
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals([], $data);

        //...unless some permissions were granted explicitely
        $this->persister->profileProperty('username', 'ROLE_USER');
        $this->persister->flush();
        $this->client->request('GET', "/api/user/{$admin->getId()}/public");
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals([], $data);

        //and the admin can see everyone.
        $this->logIn($admin);
        $this->client->request('GET', "/api/user/{$user->getId()}/public");
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(
            [
                'firstName' => 'user',
                'lastName' => 'user',
                'username' => 'user',
                'mail' => 'user@mail.com',
                'allowSendMail' => true,
                'allowSendMessage' => true,
                'id' => $user->getId(),
            ],
            $data
        );
    }

    public function testEnableUserAction()
    {
        $admin = $this->createAdmin();
        $jane = $this->persister->user('jane');
        $this->logIn($jane);
        $this->client->request('POST', "/api/user/{$jane->getId()}/enable.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->login($admin);
        $this->client->request('POST', "/api/user/{$jane->getId()}/enable.json");
        $user = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(true, $user['is_enabled']);
    }

    public function testDisableUserAction()
    {
        $admin = $this->createAdmin();
        $jane = $this->persister->user('jane');
        $this->logIn($jane);
        $this->client->request('POST', "/api/user/{$jane->getId()}/disable.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->login($admin);
        $this->client->request('POST', "/api/user/{$jane->getId()}/disable.json");
        $user = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(false, $user['is_enabled']);
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
