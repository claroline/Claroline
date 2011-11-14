<?php

namespace Claroline\SecurityBundle\Tests\Controller;

use Claroline\UserBundle\Tests\DataFixtures\ORM\LoadUserData;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CommonBundle\Library\Testing\TransactionalTestCase;

class AuthenticationTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent :: setUp();
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
        $this->submitLoginForm('jdoe', 'topsecret');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertRegexp('/[^login_failure]/i', $this->client->getResponse()->getContent());
    }

    public function testLoginWithWrongCredentialsReturnsFailureMsg()
    {
        $this->submitLoginForm('jdoe', 'BadPassword');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertRegexp('/login_failure/i', $this->client->getResponse()->getContent());
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
        $controller = new \Claroline\SecurityBundle\Controller\AuthenticationController(
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
        $controller = new \Claroline\SecurityBundle\Controller\AuthenticationController(
            $mockedRequest,
            $mockedTwig
        );
        $controller->logoutAction();
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