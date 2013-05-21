<?php

namespace Claroline\CoreBundle\Tests\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class ProfileControllerTest extends FunctionalTestCase
{
    private $logRepository;

    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->client->followRedirects();
        $this->logRepository = $this->em->getRepository('ClarolineCoreBundle:Logger\Log');
    }

    public function testLoggedUserCanEditHisProfile()
    {
        $now = new \DateTime();

        $this->loadUserData(array('user' => 'user'));
        $user = $this->getUser('user');
        $this->logUser($user);
        $crawler = $this->client->request('GET', '/profile/form');

        // check user has user role and cannot change it
        $readonly = $crawler->filter('#profile_form_platformRoles')->attr('disabled');
        $userTranslation = $this->client->getContainer()->get('translator')->trans('user', array(), 'platform');
        $selected = $crawler->filter('option:contains("'.$userTranslation.'")')->attr('selected');
        $this->assertEquals('disabled', $readonly);
        $this->assertEquals('selected', $selected);

        // change credentials
        $crawler = $this->client->submit(
            $crawler->filter('button[type=submit]')->form(), array(
            'profile_form[username]' => 'new_username',
            'profile_form[plainPassword][first]' => 'new_password',
            'profile_form[plainPassword][second]' => 'new_password'
            )
        );
        // log with new credentials
        $this->client->request('GET', '/logout');
        $user->setUsername('new_username');
        $user->setPlainPassword('new_password');
        $crawler = $this->logUser($user);
        $this->assertEquals(0, $crawler->filter('#login-error')->count());

        $logs = $this->logRepository->findActionAfterDate(
            'user_update',
            $now,
            $user->getId(),
            null,
            null,
            $user->getId()
        );
        $this->assertEquals(1, count($logs));
    }

    public function testPublicProfileCanBeSeenByOtherUsers()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $adminId = $this->getUser('admin')->getId();
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/profile/view/{$adminId}");
        $this->assertRegExp(
            '/admin/s', $this->client->getResponse()->getContent()
        );
    }
}