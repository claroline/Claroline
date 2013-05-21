<?php

namespace Claroline\CoreBundle\Tests\Controller;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class RegistrationControllerTest extends FunctionalTestCase
{
    /** @var Claroline\CoreBundle\Library\Testing\PlatformTestConfigurationHandler */
    private $configHandler;

    private $logRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user'));
        $this->client->followRedirects();
        $this->configHandler = $this->client
            ->getContainer()
            ->get('claroline.config.platform_config_handler');
        $this->configHandler->eraseTestConfiguration();
        $this->logRepository = $this->em->getRepository('ClarolineCoreBundle:Logger\Log');
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->configHandler->eraseTestConfiguration();
    }

    public function testUserCannotBeRegisteredByUnauthorizedUser()
    {
        $now = new \DateTime();

        $this->logUser($this->getUser('user'));
        $this->client->request('GET', '/register/form');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        $logs = $this->logRepository->findActionAfterDate(
            'user_create',
            $now,
            $this->getUser('user')->getId()
        );
        $this->assertEquals(0, count($logs));
    }

    public function testUserCanBeRegisteredByAuthorizedUser()
    {
        $now = new \DateTime();

        $rm = $this->client->getContainer()->get('claroline.security.right_manager');
        $user = $this->getUser('user');
        $rm->addRight('Claroline\CoreBundle\Entity\User', $user, MaskBuilder::MASK_CREATE);
        $this->logUser($user);
        $this->registerUser('Bill', 'Doe', 'bdoe', '123');
        $crawler = $this->logUser($this->getUser('bdoe'));
        $this->assertEquals(0, $crawler->filter('#login-error')->count());

        $logs = $this->logRepository->findActionAfterDate(
            'user_create',
            $now,
            $this->getUser('user')->getId(),
            null,
            null,
            $this->getUser('bdoe')->getId()
        );
        $this->assertEquals(1, count($logs));
    }

    public function testAnonymousUserCanRegisterHimselfOnlyIfOptionIsEnabled()
    {
        $now = new \DateTime();

        $this->configHandler->setParameter('allow_self_registration', false);
        $this->client->request('GET', '/register/form');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->configHandler->setParameter('allow_self_registration', true);
        $this->registerUser('Bill', 'Doe', 'bdoe', '123');
        $crawler = $this->logUser($this->getUser('bdoe'));
        $this->assertEquals(0, $crawler->filter('#login-error')->count());

        $logs = $this->logRepository->findActionAfterDate(
            'user_create',
            $now,
            null,
            null,
            null,
            $this->getUser('bdoe')->getId(),
            null,
            null,
            null,
            'anonymous'
        );
        $this->assertEquals(1, count($logs));
    }

    public function testSelfRegisteredUserHasOneRepository()
    {
        $now = new \DateTime();

        $this->configHandler->setParameter('allow_self_registration', true);
        $this->registerUser('Bill', 'Doe', 'bdoe', '123');
        $user = $this->getUser('bdoe');
        $repositoryWs = $user->getPersonalWorkspace();
        $this->assertEquals(1, count($repositoryWs));

        $logs = $this->logRepository->findActionAfterDate(
            'user_create',
            $now,
            null,
            null,
            null,
            $this->getUser('bdoe')->getId(),
            null,
            null,
            null,
            'anonymous'
        );
        $this->assertEquals(1, count($logs));
    }

    private function registerUser($firstName, $lastName, $username, $password)
    {
        $crawler = $this->client->request('GET', '/register/form');
        $form = $crawler->filter('button[type=submit]')->form();
        $form['profile_form[firstName]'] = $firstName;
        $form['profile_form[lastName]'] = $lastName;
        $form['profile_form[username]'] = $username;
        $form['profile_form[plainPassword][first]'] = $password;
        $form['profile_form[plainPassword][second]'] = $password;

        return $this->client->submit($form);
    }

    private function getUser($username)
    {
        // TODO : find out why a new call to the container is necessary
        // ($this->getEntityManager() doesn't work)
        $user = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:User')
            ->findOneByUsername($username);

        return $user;
    }
}