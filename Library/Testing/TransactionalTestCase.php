<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Client;

abstract class TransactionalTestCase extends WebTestCase
{
    /** @var TransactionalTestClient */
    protected $client;

    protected function setUp()
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->client->beginTransaction();
    }

    protected function tearDown()
    {
        $this->client->shutdown();
        parent::tearDown();
    }

    protected function logIn(User $user, $firewall = 'main')
    {
        $this->client = $this->logClient($user, $this->client, $firewall);
    }

    private function logClient(User $user, Client $client, $firewall = 'main')
    {
        $tokenStorage = $client->getContainer()->get('security.token_storage');
        $token = new UsernamePasswordToken($user, $user->getPlainPassword(), $firewall , $user->getRoles());
        $tokenStorage->setToken($token);

        //now we generate the cookie !
        //@see http://symfony.com/doc/current/cookbook/testing/simulating_authentication.html
        $session = $client->getContainer()->get('session');
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $client;
    }
}
