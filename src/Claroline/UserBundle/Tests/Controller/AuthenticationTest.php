<?php

namespace Claroline\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Loader;
use Claroline\UserBundle\Tests\DataFixtures\ORM\LoadUserData;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class AuthenticationTest extends WebTestCase
{
    private $client;

    protected function setUp() {
        $this->client = self :: createClient();
        $this->client->followRedirects();

        $fixture = new LoadUserData();
        $fixture->setContainer($this->client->getContainer());

        $loader = new Loader();
        $loader->addFixture($fixture);
        $purger = new ORMPurger();
        $em = $this->client->getContainer()->get('doctrine')->getEntityManager();
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures());
    }

    public function test_login_should_redirect_when_valid_credentials()
    {
        $crawler = $this->client->request('GET', '/login');

        $response = $this->client->getResponse();
        $container = $this->client->getContainer();
        $logger = $container->get('logger');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($crawler->filter('div#content form input[id=_submit]')->count() > 0);

        $form = $crawler->selectButton('_submit')->form();
        $form['_username'] = 'jdoe';
        $form['_password'] = 'topsecret';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertRegexp('/desktop/i', $this->client->getResponse()->getContent());
    }
}
