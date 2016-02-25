<?php

namespace Claroline\CoreBubdle\Tests\API\User;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

/**
 * Specific tests for organizations
 * How to run:
 * - create database
 * - php app/console claroline:init_test_schema --env=test
 * - php app/console doctrine:schema:update --force --env=test
 * - bin/phpunit vendor/claroline/core-bundle/Claroline/CoreBundle/Tests/API/User/GroupControllerTest.php -c app/phpunit.xml
 */
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
        $this->groupOrga = $this->persister->group('groupOrga');
        $this->groupOrga->addRole($this->baseRole);
        $this->groupBase = $this->persister->group('groupBase');
        $this->groupOrga->addOrganization($organization);
        $this->adminOrga->addAdministratedOrganization($organization);
        $this->persister->persist($this->groupOrga);
        $this->persister->persist($this->adminOrga);
        $this->persister->persist($this->admin);
        $this->persister->flush();
    }

    //@route: api_get_groups
    //@url: /api/groups.{_format}
    public function testGroupsAction()
    {
        $this->logIn($this->admin);
        $this->client->request('GET', '/api/groups.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(2, count(json_decode($data, true)));
    }

    //@route: api_get_groups
    //@url: /api/groups.{_format}
    public function testGroupsActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('GET', '/api/groups.json');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_group
    //@url: /api/groups/{group}.{_format}
    public function testGetGroupAction()
    {
        $this->logIn($this->adminOrga);
        $this->client->request('GET', "/api/groups/{$this->groupOrga->getId()}.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('groupOrga', $data['name']);
    }

    //@route: api_get_group
    //@url: /api/groups/{group}.{_format}
    public function testGetGroupActionIsProtected()
    {
        $this->logIn($this->adminOrga);
        $this->client->request('GET', "/api/groups/{$this->groupBase->getId()}.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_post_group
    //@url: /api/groups.{_format} 
    //@method: POST
    public function testPostGroupAction()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    //@route: api_post_group
    //@url: /api/groups.{_format} 
    //@method: POST
    public function testPostGroupActionIsProtected()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }


    //@route: api_put_group
    //@url: /api/groups/{group}.{_format} 
    //@method: PUT
    public function testPutGroupAction()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    //@route: api_put_group
    //@url: /api/groups/{group}.{_format} 
    //@method: PUT
    public function testPutGroupActionIsProtected()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    //@route: api_delete_group
    //@url: /api/groups/{group}.{_format}
    //@method: DELETE
    public function testDeleteGroupAction()
    {
        $this->logIn($this->adminOrga);
        $this->client->request('DELETE', "/api/groups/{$this->groupOrga->getId()}.json");
        $data = $this->client->getResponse()->getContent();

        $this->logIn($this->admin);
        $this->client->request('GET', '/api/groups.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(1, count(json_decode($data, true)));
    }

    //@route: api_delete_group
    //@url: /api/groups/{group}.{_format}
    //@method: DELETE
    public function testDeleteGroupActionIsProtected()
    {
        $this->login($this->john);
        $this->client->request('DELETE', "/api/groups/{$this->groupOrga->getId()}.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_delete_groups
    //@url: /api/groups.{_format}
    //@method: DELETE
    public function testDeleteGroupsAction()
    {
        $this->logIn($this->adminOrga);
        $this->client->request('DELETE', "/api/groups.json?groupIds[]={$this->groupOrga->getId()}");
        $data = $this->client->getResponse()->getContent();

        $this->logIn($this->admin);
        $this->client->request('GET', '/api/groups.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(1, count(json_decode($data, true)));
    }

    //@route: api_delete_groups
    //@url: /api/groups.{_format}
    //@method: DELETE
    public function testDeleteGroupsActionIsProtected()
    {
        $this->logIn($this->adminOrga);
        $this->client->request('DELETE', "/api/groups.json?groupIds[]={$this->groupOrga->getId()}&groupIds[]={$this->groupBase->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    //@route: api_add_group_role
    //@url: /api/groups/{group}/roles/{role}/add.{_format}
    //@method: PATCH
    public function testAddGroupRoleAction()
    {
        $this->logIn($this->adminOrga);
        $this->client->request('PATCH', "/api/groups/{$this->groupOrga->getId()}/roles/{$this->teacherRole->getId()}/add.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(2, count($data['roles']));
    }

    //@route: api_add_group_role
    //@url: /api/groups/{group}/roles/{role}/add.{_format}
    //@method: PATCH
    public function testAddGroupRoleActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('PATCH', "/api/groups/{$this->groupOrga->getId()}/roles/{$this->teacherRole->getId()}/add.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    } 

    //@route: api_remove_group_role
    //@url: /api/groups/{group}/roles/{role}/remove.{_format}
    //@method: GET (this looks wrong to me)
    public function testRemoveGroupRoleAction()
    {
        $this->logIn($this->adminOrga);
        $this->client->request('PATCH', "/api/groups/{$this->groupOrga->getId()}/roles/{$this->teacherRole->getId()}/remove.json");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(0, count($data['roles']));
    }

    //@route: api_remove_group_role
    //@url: /api/groups/{group}/roles/{role}/remove.{_format}
    //@method: GET (this looks wrong to me)
    public function testRemoveGroupRoleActionIsProtected()
    {
        $this->logIn($this->john);
        $this->client->request('GET', "/api/groups/{$this->groupOrga->getId()}/roles/{$this->teacherRole->getId()}/remove.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_search_groups
    //@url: /api/searches/{page}/groups/{limit}.{_format}
    public function testGetSearchGroupsAction()
    {
        $url = '/api/searches/0/groups/10.json';
        //ADMINISTRATOR USAGE !
        $this->logIn($this->admin);

        //base search should retrieve everything for the administrator
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(2, count($data['groups']));

        //now we're adding a simple filter
        $this->client->request('GET', $url . '?name[]=base');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(1, count($data['groups']));

        //ORGANIZATION MANAGER USAGE !
        $this->logIn($this->adminOrga);

        //base search should retrive all the users of my organizations
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(1, count($data['groups']));

        //USER USAGE
        $this->logIn($this->john);

        //base search should retrive all the users of my organizations. If I don't have any, it should be 0
        $this->client->request('GET', $url);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(0, count($data['groups']));
    }

    //@route: api_get_group_searchable_fields
    //@url: /api/group/searchable/fields.{_format}
    public function testGetGroupSearchableFieldsAction()
    {
        $this->logIn($this->admin);
        $this->client->request('GET', '/api/group/searchable/fields.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertGreaterThan(0, count($data));
    }

    //@route: api_get_create_group_form
    //@url: ANY  /api/create/group/form.{_format}
    public function testGetCreateGroupFormAction()
    {
        $this->logIn($this->john);
        $this->client->request('GET', '/api/create/group/form.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_edit_group_form
    //@url: /api/edits/{group}/group/form.{_format}
    public function testGetEditGroupFormAction()
    {
        $this->logIn($this->adminOrga);
        $this->client->request('GET', "/api/edits/{$this->groupOrga->getId()}/group/form.json");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //@route: api_get_edit_group_form
    //@url: /api/edits/{group}/group/form.{_format}
    public function testGetEditGroupFormActionisProtected()
    {
        $this->logIn($this->john);
        $this->client->request('GET', "/api/edits/{$this->groupOrga->getId()}/group/form.json");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}