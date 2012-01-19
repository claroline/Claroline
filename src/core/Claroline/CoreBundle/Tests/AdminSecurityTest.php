<?php

namespace Claroline\CoreBundle;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CoreBundle\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\ORM\LoadUserData;

class AdminSecurityTest extends TransactionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $refRepo = new ReferenceRepository($em);
        $userFixture = new LoadUserData();
        $userFixture->setContainer($this->client->getContainer());
        $userFixture->setReferenceRepository($refRepo);
        $userFixture->load($em);
        
        $this->client->followRedirects();
    }
    
    public function testAdminSectionRequiresAuthenticatedUser()
    {
        $crawler = $this->client->request('GET', '/admin');
        $this->assertTrue($crawler->filter('#login_form')->count() > 0);
    }
    
    public function testAccessToAdminSectionIsDeniedToSimpleUsers()
    {
        $this->logUser('user', '123');
        $this->client->request('GET', '/admin');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testAccessToAdminSectionIsAllowedToAdminUsers()
    {
        $this->logUser('admin', '123');
        $crawler = $this->client->request('GET', '/admin');
        $this->assertTrue($crawler->filter('#administration.section')->count() > 0);
    }
    
    private function logUser($username, $password)
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('#login_form input[type=submit]')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $this->client->submit($form);
    }
}