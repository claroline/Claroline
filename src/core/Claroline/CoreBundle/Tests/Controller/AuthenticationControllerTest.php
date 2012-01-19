<?php

namespace Claroline\CoreBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CoreBundle\Tests\DataFixtures\ORM\LoadUserData;
use Claroline\CoreBundle\Testing\TransactionalTestCase;

class AuthenticationControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $refRepo = new ReferenceRepository($em);

        $fixture = new LoadUserData();
        $fixture->setContainer($this->client->getContainer());
        $fixture->setReferenceRepository($refRepo);
        $fixture->load($em);
    }

    public function testLoginWithValidCredentialsDoesntReturnFailureMsg()
    {
        $crawler = $this->logUser('user', '123');
        $this->assertEquals(0, $crawler->filter('#login_form .failure_msg')->count());
    }

    public function testLoginWithWrongCredentialsReturnsFailureMsg()
    {
        $crawler = $this->logUser('jdoe', 'BadPassword');        
        $this->assertEquals(1, $crawler->filter('#login_form .failure_msg')->count());
    }

    public function testADirectCallToCheckMethodThrowsAnException()
    {
        $this->setExpectedException('RuntimeException');
        $mockedRequest = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $mockedTwig = $this->getMockBuilder('Symfony\Bundle\TwigBundle\TwigEngine')
            ->disableOriginalConstructor()
            ->getMock();
        $controller = new \Claroline\CoreBundle\Controller\AuthenticationController(
            $mockedRequest,
            $mockedTwig
        );
        $controller->checkAction();
    }
    
    public function testADirectCallToLogoutMethodThrowsAnException()
    {
        $this->setExpectedException('RuntimeException');
        $mockedRequest = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $mockedTwig = $this->getMockBuilder('Symfony\Bundle\TwigBundle\TwigEngine')
            ->disableOriginalConstructor()
            ->getMock();
        $controller = new \Claroline\CoreBundle\Controller\AuthenticationController(
            $mockedRequest,
            $mockedTwig
        );
        $controller->logoutAction();
    }
    
    private function logUser($username, $password)
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('#login_form input[type=submit]')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        
        return $this->client->submit($form);
    }
}