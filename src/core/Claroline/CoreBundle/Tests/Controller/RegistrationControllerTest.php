<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CoreBundle\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\ORM\LoadUserData;

class RegistrationControllerTest extends TransactionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $em = $this->client->getContainer()->get('doctrine')->getEntityManager();
        $refRepo = new ReferenceRepository($em);
        $userFixture = new LoadUserData();
        $userFixture->setContainer($this->client->getContainer());
        $userFixture->setReferenceRepository($refRepo);
        $userFixture->load($em);
        
        $this->client->followRedirects();
    }
    
    public function testUserCanBeRegisteredByAdmin()
    {
        $this->logUser('admin', '123');
        $this->registerUser('Bill', 'Doe', 'bdoe', '123');
        $crawler = $this->logUser('bdoe', '123');
        $this->assertEquals(0, $crawler->filter('#login_form .failure_msg')->count());
    }
    
    public function testUserCannotBeRegisteredByUnauthorizedUser()
    {
        $this->logUser('user', '123');
        $this->client->request('GET', '/user/register');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testUserCanBeRegisteredByAuthorizedUser()
    {
        $userFqcn = 'Claroline\CoreBundle\Entity\User';
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository($userFqcn)->findOneByUsername('user');
        $rm = $this->client->getContainer()->get('claroline.security.right_manager');
        $rm->addRight($userFqcn, $user, MaskBuilder::MASK_CREATE);
        
        $this->logUser('user', '123');
        $this->registerUser('Bill', 'Doe', 'bdoe', '123');
        $crawler = $this->logUser('bdoe', '123');
        $this->assertEquals(0, $crawler->filter('#login_form .failure_msg')->count());
    }
    
    public function testSelfRegistrationAttemptThrowsAnExceptionIfNotAllowed()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');
        $this->getControllerPreparedForAnonymousUser(false)->createAction();
    }
    
    private function logUser($username, $password)
    {
        $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('input[type=submit]')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        
        return $this->client->submit($form);
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
}