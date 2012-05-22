<?php

namespace Claroline\CoreBundle\Tests\Controller;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class RegistrationControllerTest extends FunctionalTestCase
{
    /** @var Claroline\CoreBundle\Library\Testing\PlatformTestConfigurationHandler */
    private $configHandler;
    
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->client->followRedirects();
        $this->configHandler = $this->client
            ->getContainer()
            ->get('claroline.config.platform_config_handler');
        $this->configHandler->eraseTestConfiguration();
    }
    
    protected function tearDown()
    {
        parent::tearDown();
        $this->configHandler->eraseTestConfiguration();
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
    
    public function testAnonymousUserCanRegisterHimselfOnlyIfOptionIsEnabled()
    {
        $this->configHandler->setParameter('allow_self_registration', false);        
        $this->client->request('GET', '/user/register');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->configHandler->setParameter('allow_self_registration', true);        
        $this->registerUser('Bill', 'Doe', 'bdoe', '123');
        $crawler = $this->logUser($this->getUser('bdoe'));
        $this->assertEquals(0, $crawler->filter('#login_form .failure_msg')->count());
    }
    
    public function testSelfRegisteredUserHasOneRepository()
    {
        $this->configHandler->setParameter('allow_self_registration', true);        
        $this->registerUser('Bill', 'Doe', 'bdoe', '123');
        $user = $this->getUser(('bdoe'));
        $repositoryWs = $user->getPersonnalWorkspace();
        $this->assertEquals(1, count($repositoryWs));
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
    
    private function getUser($username)
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('Claroline\CoreBundle\Entity\User')
            ->findOneByUsername($username);
        
        return $user;
    }
}