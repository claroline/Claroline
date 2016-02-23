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

    public function testGroupsAction()
    {

    }

    public function testGroupsActionIsProtected()
    {
        
    }

    public function testGetGroupAction()
    {

    }

    public function testGetGroupActionIsProtected()
    {

    }

    public function testPostGroupAction()
    {

    }

    public function testPostGroupActionIsProtected()
    {
        
    }

    public function testPutGroupAction()
    {

    }

    public function testPutGroupActionIsProtected()
    {
        
    }

    public function testDeleteGroupAction()
    {

    }

    public function testDeleteGroupActionIsProtected()
    {

    }

    public function testDeleteGroupsAction()
    {

    }

    public function testDeleteGroupsActionIsProtected()
    {
        
    }

    public function testAddGroupRoleAction()
    {

    }

    public function testAddGroupRoleActionIsProtected()
    {

    } 

    public function testRemoveGroupRoleAction()
    {

    }

    public function testRemoveGroupRoleActionIsProtected()
    {
        
    }

    public function testGetSearchGroupsAction()
    {

    }

    public function testGetSearchGroupsActionIsProtected()
    {
        
    }

    public function testGetGroupSearchableFieldsAction()
    {

    }

    public function testGetCreateGroupFormAction()
    {

    }

    public function testGetCreateGroupFormActionIsProtected()
    {

    }

    public function testGetEditGroupFormAction()
    {

    }

    public function testGetEditGroupFormActionisProtected()
    {
        
    }
}