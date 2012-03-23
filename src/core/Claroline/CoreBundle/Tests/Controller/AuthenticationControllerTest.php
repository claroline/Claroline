<?php
namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\User;

class AuthenticationControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->client->followRedirects();
    }

    public function testLoginWithValidCredentialsDoesntReturnFailureMsg()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/user'));
        $this->assertEquals(0, $crawler->filter('#login_form .failure_msg')->count());
    }

    public function testLoginWithWrongCredentialsReturnsFailureMsg()
    {
        $unknownUser = new User();
        $unknownUser->setUsername('unknown_user');
        $unknownUser->setPlainPassword('bad_password');
        $crawler = $this->logUser($unknownUser);        
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
}