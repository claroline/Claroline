<?php

namespace Claroline\CoreBundle\Tests\API\User;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class GroupControllerTest extends TransactionalTestCase
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
    /** @var Role */
    private $teacherRole;
    /** @var Role */
    private $baseRole;

    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    //@route: api_get_groups
    //@url: /api/groups.{_format}
    public function testGroupsAction()
    {
        //initialization
        $admin = $this->createAdmin();
        $this->persister->group('grp1');
        $this->persister->group('grp2');
        $this->persister->flush();

        //tests
        $this->logIn($admin);
        $this->client->request('GET', '/api/groups.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(2, count(json_decode($data, true)));
    }

    //@route: api_get_groups
    //@url: /api/groups.{_format}
    public function testGroupsActionIsProtected()
    {
        //initialization
        $john = $this->persister->user('john');
        $this->persister->flush();

        //tests
        $this->logIn($john);
        $this->client->request('GET', '/api/groups.json');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_group
    //@url: /api/groups/{group}.{_format}
    public function testGetGroupAction()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $groupOrga = $this->createGroupOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('GET', "/api/groups/{$groupOrga->getId()}.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('groupOrga', $data['name']);
    }

    //@route: api_get_group
    //@url: /api/groups/{group}.{_format}
    public function testGetGroupActionIsProtected()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $groupBase = $this->persister->group('groupBase');
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('GET', "/api/groups/{$groupBase->getId()}.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_post_group
    //@url: /api/groups.{_format}
    //@method: POST
    public function testPostGroupAction()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    //@route: api_post_group
    //@url: /api/groups.{_format}
    //@method: POST
    public function testPostGroupActionIsProtected()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    //@route: api_put_group
    //@url: /api/groups/{group}.{_format}
    //@method: PUT
    public function testPutGroupAction()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    //@route: api_put_group
    //@url: /api/groups/{group}.{_format}
    //@method: PUT
    public function testPutGroupActionIsProtected()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    //@route: api_delete_group
    //@url: /api/groups/{group}.{_format}
    //@method: DELETE
    public function testDeleteGroupAction()
    {
        //initialization
        $admin = $this->createAdmin();
        $organization = $this->persister->organization('organization');
        $adminOrga = $this->createAdminOrga($organization);
        $groupOrga = $this->createGroupOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('DELETE', "/api/groups/{$groupOrga->getId()}.json");
        $data = $this->client->getResponse()->getContent();
        $this->logIn($admin);
        $this->client->request('GET', '/api/groups.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(0, count(json_decode($data, true)));
    }

    //@route: api_delete_group
    //@url: /api/groups/{group}.{_format}
    //@method: DELETE
    public function testDeleteGroupActionIsProtected()
    {
        //initialization
        $john = $this->persister->user('john');
        $group = $this->persister->group('group');
        $this->persister->flush();

        //tests
        $this->login($john);
        $this->client->request('DELETE', "/api/groups/{$group->getId()}.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_delete_groups
    //@url: /api/groups.{_format}
    //@method: DELETE
    public function testDeleteGroupsAction()
    {
        //initialization
        $admin = $this->createAdmin();
        $organization = $this->persister->organization('organization');
        $groupOrga = $this->createGroupOrga($organization);
        $adminOrga = $this->createAdminOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('DELETE', "/api/groups.json?groupIds[]={$groupOrga->getId()}");
        $data = $this->client->getResponse()->getContent();
        $this->logIn($admin);
        $this->client->request('GET', '/api/groups.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(0, count(json_decode($data, true)));
    }

    //@route: api_delete_groups
    //@url: /api/groups.{_format}
    //@method: DELETE
    public function testDeleteGroupsActionIsProtected()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $groupOrga = $this->createGroupOrga($organization);
        $adminOrga = $this->createAdminOrga($organization);
        $groupBase = $this->persister->group('groupBase');
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('DELETE', "/api/groups.json?groupIds[]={$groupOrga->getId()}&groupIds[]={$groupBase->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_add_group_role
    //@url: /api/groups/{group}/roles/{role}/add.{_format}
    //@method: PATCH
    public function testAddGroupRoleAction()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $groupOrga = $this->createGroupOrga($organization);
        $adminOrga = $this->createAdminOrga($organization);
        $teacherRole = $this->persister->role('ROLE_TEACHER');
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('PATCH', "/api/groups/{$groupOrga->getId()}/roles/{$teacherRole->getId()}/add.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(2, count($data['roles']));
    }

    //@route: api_add_group_role
    //@url: /api/groups/{group}/roles/{role}/add.{_format}
    //@method: PATCH
    public function testAddGroupRoleActionIsProtected()
    {
        //initialization
        $john = $this->persister->user('john');
        $teacherRole = $this->persister->role('ROLE_TEACHER');
        $organization = $this->persister->organization('organization');
        $groupOrga = $this->createGroupOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($john);
        $this->client->request('PATCH', "/api/groups/{$groupOrga->getId()}/roles/{$teacherRole->getId()}/add.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_remove_group_role
    //@url: /api/groups/{group}/roles/{role}/remove.{_format}
    //@method: GET (this looks wrong to me)
    public function testRemoveGroupRoleAction()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $groupOrga = $this->createGroupOrga($organization);
        $adminOrga = $this->createAdminOrga($organization);
        $teacherRole = $this->persister->role('ROLE_TEACHER');
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('PATCH', "/api/groups/{$groupOrga->getId()}/roles/{$teacherRole->getId()}/remove.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(0, count($data['roles']));
    }

    //@route: api_remove_group_role
    //@url: /api/groups/{group}/roles/{role}/remove.{_format}
    //@method: GET (this looks wrong to me)
    public function testRemoveGroupRoleActionIsProtected()
    {
        //initialization
        $john = $this->persister->user('john');
        $teacherRole = $this->persister->role('ROLE_TEACHER');
        $organization = $this->persister->organization('organization');
        $groupOrga = $this->createGroupOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($john);
        $this->client->request('GET', "/api/groups/{$groupOrga->getId()}/roles/{$teacherRole->getId()}/remove.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_search_groups
    //@url: /api/groups/page/{page}/limit/{limit}/search.{_format}
    public function testGetSearchGroupsAction()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $this->createGroupOrga($organization);
        $adminOrga = $this->createAdminOrga($organization);
        $this->persister->group('groupBase');
        $john = $this->persister->user('john');
        $admin = $this->createAdmin();

        $url = '/api/groups/page/0/limit/10/search.json';
        //ADMINISTRATOR USAGE !
        $this->logIn($admin);

        //base search should retrieve everything for the administrator
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(2, count($data['groups']));

        //now we're adding a simple filter
        $this->client->request('GET', $url.'?name[]=base');
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = json_decode($data, true);
        $this->assertEquals(1, count($data['groups']));

        //ORGANIZATION MANAGER USAGE !
        $this->logIn($adminOrga);

        //base search should retrieve all the users of my organizations
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($data['groups']));

        //USER USAGE
        $this->logIn($john);

        //base search should retrieve all the users of my organizations. If I don't have any, it should be 0
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, count($data['groups']));
    }

    //@route: api_get_group_searchable_fields
    //@url: /api/group/searchable/fields.{_format}
    public function testGetGroupSearchableFieldsAction()
    {
        $this->createAdmin();
        $this->persister->flush();
        $this->client->request('GET', '/api/group/searchable/fields.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertGreaterThan(0, count($data));
    }

    //@route: api_get_group_create_form
    //@url: ANY  /api/group/create/form.json
    public function testGetCreateGroupFormAction()
    {
        //initialization
        $john = $this->persister->user('john');

        //tests
        $this->logIn($john);
        $this->client->request('GET', '/api/group/create/form.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_group_edit_form
    //@url: /api/edits/{group}/group/form.{_format}
    public function testGetEditGroupFormAction()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $groupOrga = $this->createGroupOrga($organization);
        $adminOrga = $this->createAdminOrga($organization);
        $this->persister->flush();

        //tests
        $this->logIn($adminOrga);
        $this->client->request('GET', "/api/group/{$groupOrga->getId()}/edit/form.json");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_group_edit_form
    //@url: /api/group/{group}/edit/form.{_format}
    public function testGetEditGroupFormActionisProtected()
    {
        //initialization
        $organization = $this->persister->organization('organization');
        $groupOrga = $this->createGroupOrga($organization);
        $john = $this->persister->user('john');
        $this->persister->flush();

        $this->logIn($john);
        $this->client->request('GET', "/api/group/{$groupOrga->getId()}/edit/form.json");
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

    private function createGroupOrga($organization)
    {
        $baseRole = $this->persister->role('ROLE_BASE');
        $groupOrga = $this->persister->group('groupOrga');
        $groupOrga->addRole($baseRole);
        $groupOrga->addOrganization($organization);

        return $groupOrga;
    }

    private function createAdminOrga($organization)
    {
        $adminOrga = $this->persister->user('adminOrga');
        $adminOrga->addAdministratedOrganization($organization);

        return $adminOrga;
    }
}
