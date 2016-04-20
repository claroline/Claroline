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
        // we can't simply do "$client->shutdown()" because sometimes
        // when an integration test fails (e.g. due to an error in the
        // container configuration) the $client property is set to null
        // (by PHPUnit?) and the original error is hidden behind a fatal
        // "Call to a member function shutdown() on a non-object"...
        if ($this->client instanceof TransactionalTestClient) {
            $this->client->shutdown();
        }

        // the following helps to free memory and speed up test suite execution.
        // see http://kriswallsmith.net/post/18029585104/faster-phpunit
        $refl = new \ReflectionObject($this);

        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }

        parent::tearDown();
    }

    protected function logIn(User $user, $firewall = 'main')
    {
        $this->logClient($user, $this->client, $firewall);
    }

    private function logClient(User $user, Client $client, $firewall = 'main')
    {
        $tokenStorage = $client->getContainer()->get('security.token_storage');
        $token = new UsernamePasswordToken($user, $user->getPlainPassword(), $firewall, $user->getRoles());
        $tokenStorage->setToken($token);

        //now we generate the cookie !
        //@see http://symfony.com/doc/current/cookbook/testing/simulating_authentication.html
        $session = $client->getContainer()->get('session');
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $client;
    }

    public function setPlatformOption($parameter, $value)
    {
        $ch = $this->client->getContainer()->get('claroline.config.platform_config_handler');
        $ch->setParameter($parameter, $value);
    }
}
