<?php

namespace Claroline\SecurityBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\UserBundle\Tests\DataFixtures\ORM\LoadUserData;
use Doctrine\Common\DataFixtures\ReferenceRepository;

class AuthenticationTest extends WebTestCase
{
    private $client;

    protected function setUp()
    {
        $this->client = self :: createClient();
        $this->client->followRedirects();
        $this->client->beginTransaction();

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $refRepo = new ReferenceRepository($em);

        $fixture = new LoadUserData();
        $fixture->setContainer($this->client->getContainer());
        $fixture->setReferenceRepository($refRepo);
        $fixture->load($em);
    }

    protected function tearDown()
    {
       $this->client->rollBack();
    }

    public function test_login_with_valid_credentials_doesnt_return_failure_msg()
    {
        $this->submitLoginForm('jdoe', 'topsecret');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertRegexp('/[^login_failure]/i', $this->client->getResponse()->getContent());
    }

    public function test_login_with_wrong_credentials_returns_failure_msg()
    {
        $this->submitLoginForm('jdoe', 'BadPassword');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertRegexp('/login_failure/i', $this->client->getResponse()->getContent());
    }

    public function testADirectCallToCheckMethodThrowsAnException()
    {
        $this->setExpectedException('RuntimeException');
        $this->client
            ->getContainer()
            ->get('claroline.security.authentication_controller')
            ->checkAction();
    }
    
    public function testADirectCallToLogoutMethodThrowsAnException()
    {
        $this->setExpectedException('RuntimeException');
        $this->client
            ->getContainer()
            ->get('claroline.security.authentication_controller')
            ->logoutAction();
    }
    
    private function submitLoginForm($username, $password)
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('_submit')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;

        return $this->client->submit($form);
    }
}