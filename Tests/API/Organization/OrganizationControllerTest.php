<?php

namespace Claroline\CoreBundle\Tests\API\Organization;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

/**
 * Specific tests for organizations
 * How to run:
 * - create database
 * - php app/console claroline:init_test_schema --env=test
 * - php app/console doctrine:schema:update --force --env=test
 * - bin/phpunit vendor/claroline/core-bundle/Claroline/CoreBundle/Tests/API/Organization/OrganizationControllerTest.php -c app/phpunit.xml
 */
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

    public function testPostOrganizationAction()
    {

    }

    public function testPostOrganizationActionIsProtected()
    {
        
    }

    public function testDeleteOrganizationAction()
    {

    }

    public function testDeleteOrganizationActionIsProtected()
    {
        
    }

    public function testGetOrganizationsAction()
    {
        $this->persister->organization('orga1');
        $this->persister->organization('orga2');
        $this->persister->flush();
        $this->logIn($this->admin);
        $this->client->request('GET', '/api/organizations.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(2, count(json_decode($data, true)));
    }

    public function testGetOrganizationsActionIsProtected()
    {
        $this->persister->organization('orga1');
        $this->persister->organization('orga2');
        $this->persister->flush();
        $this->logIn($this->admin);
        $this->client->request('GET', '/api/organizations.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $this->assertEquals(2, count(json_decode($data, true)));
    }

    public function testGetOrganizationListAction()
    {

    }

    public function testGetOrganizationListActionIsProtected()
    {
        
    }

    public function testGetEditOrganizationFormAction()
    {

    }

    public function testGetEditOrganizationFormActionIsProtected()
    {

    }

    public function testPutOrganizationAction()
    {

    }

    public function testPutOrganizationActionIsProtected()
    {

    }
}