<?php

namespace Claroline\DesktopBundle;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CommonBundle\Test\TransactionalTestCase;
use Claroline\UserBundle\Tests\DataFixtures\ORM\LoadUserData;

class SecurityTest extends TransactionalTestCase
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
    
    public function testDesktopSectionRequiresAuthenticatedUser()
    {
        $crawler = $this->client->request('GET', '/desktop');        
        $this->assertTrue($crawler->filter('#login_form')->count() > 0);
    }
    
    public function testAccessToDesktopSectionIsAllowedToSimpleUsers()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('#login_form input[type=submit]')->form();
        $form['_username'] = 'user';
        $form['_password'] = '123';
        $this->client->submit($form);
        
        $crawler = $this->client->request('GET', '/desktop');
        $this->assertTrue($crawler->filter('#desktop.section')->count() > 0);
    }
}