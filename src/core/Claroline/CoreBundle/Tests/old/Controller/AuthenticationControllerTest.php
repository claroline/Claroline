<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\User;

class AuthenticationControllerTest extends FunctionalTestCase
{
    private $logRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->client->followRedirects();
        $this->logRepository = $this->em->getRepository('ClarolineCoreBundle:Logger\Log');
    }

    public function testLoginWithValidCredentialsDoesntReturnFailureMsg()
    {
        $now = new \DateTime();

        $this->loadUserData(array('user' => 'user'));
        $crawler = $this->logUser($this->getUser('user'));
        $this->assertEquals(0, $crawler->filter('#login-error')->count());

        $logs = $this->logRepository->findActionAfterDate(
            'user_login',
            $now,
            $this->getUser('user')->getId()
        );
        $this->assertEquals(1, count($logs));
    }

    public function testLoginWithWrongCredentialsReturnsFailureMsg()
    {
        $unknownUser = new User();
        $unknownUser->setUsername('unknown_user');
        $unknownUser->setPlainPassword('bad_password');
        $crawler = $this->logUser($unknownUser);
        $this->assertEquals(1, $crawler->filter('#login-error')->count());
    }
}