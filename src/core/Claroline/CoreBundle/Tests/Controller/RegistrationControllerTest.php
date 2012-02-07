<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Testing\FunctionalTestCase;

class RegistrationControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->client->followRedirects();
    }
    
    public function testUserCanBeRegisteredByAdmin()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->registerUser('Bill', 'Doe', 'bdoe', '123');
        $crawler = $this->logUser($this->getUser('bdoe'));
        $this->assertEquals(0, $crawler->filter('#login_form .failure_msg')->count());
    }
    
    public function testUserCannotBeRegisteredByUnauthorizedUser()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', '/user/register');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testUserCanBeRegisteredByAuthorizedUser()
    {
        $rm = $this->client->getContainer()->get('claroline.security.right_manager');
        $user = $this->getFixtureReference('user/user');
        $rm->addRight('Claroline\CoreBundle\Entity\User', $user, MaskBuilder::MASK_CREATE);    
        
        $this->logUser($user);
        $this->registerUser('Bill', 'Doe', 'bdoe', '123');
        
        $crawler = $this->logUser($this->getUser('bdoe'));
        $this->assertEquals(0, $crawler->filter('#login_form .failure_msg')->count());
    }
    
    public function testSelfRegistrationAttemptThrowsAnExceptionIfNotAllowed()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');
        $this->getControllerPreparedForAnonymousUser(false)->createAction();
    }
    
    private function registerUser($firstName, $lastName, $username, $password)
    {
        $crawler = $this->client->request('GET', '/user/register');
        $form = $crawler->filter('input[type=submit]')->form();
        $form['user_form[firstName]'] = $firstName;
        $form['user_form[lastName]'] = $lastName;
        $form['user_form[username]'] = $username;
        $form['user_form[plainPassword][first]'] = $password;
        $form['user_form[plainPassword][second]'] = $password;
        
        return $this->client->submit($form);
    }
    
    private function getControllerPreparedForAnonymousUser($isSelfRegistrationAllowed)
    {
        $mockedRequest = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();     
        $mockedToken = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\AbstractToken')
            ->disableOriginalConstructor()
            ->getMock();
        $mockedToken->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue('anon.'));        
        $mockedContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $mockedContext->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(false));        
        $mockedContext->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($mockedToken));
        
        return new RegistrationController(
            $mockedRequest,
            $mockedContext,
            $this->client->getContainer()->get('form.factory'),
            $this->client->getContainer()->get('templating'),
            $this->client->getContainer()->get('translator'),
            $this->client->getContainer()->get('claroline.user.manager'),
            $isSelfRegistrationAllowed
        );
    }
    
    private function getUser($username)
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('Claroline\CoreBundle\Entity\User')
            ->findOneByUsername($username);
        
        return $user;
    }
}