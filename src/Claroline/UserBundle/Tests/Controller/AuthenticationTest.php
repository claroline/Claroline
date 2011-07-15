<?php

namespace Claroline\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Loader;
use Claroline\UserBundle\Tests\DataFixtures\ORM\LoadUserData;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;

class AuthenticationTest extends WebTestCase
{
    private $client;

    protected function setUp() {
        $this->client = self :: createClient();
        $this->client->followRedirects();
        $this->client->beginTransaction();

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $ref_repo = new ReferenceRepository($em);

        $fixture = new LoadUserData();
        $fixture->setContainer($this->client->getContainer());
        $fixture->setReferenceRepository($ref_repo);
        $fixture->load($em);
    }

    protected function tearDown() {
       $this->client->rollBack();
    }

    public function test_create_an_account_and_login()
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->filter('input[type=submit]')->form();
        $form['user[firstName]'] = 'Jean-Paul';
        $form['user[lastName]'] = 'Sartre';
        $form['user[username]'] = 'JPS';
        $form['user[plainPassword][first]'] = 'Nausee';
        $form['user[plainPassword][second]'] = 'Nausee';

        $this->client->submit($form);

        $crawler = $this->client->request('GET', '/login');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($crawler->filter('div#content form input[id=_submit]')->count() > 0);

        $form = $crawler->selectButton('_submit')->form();
        $form['_username'] = 'JPS';
        $form['_password'] = 'Nausee';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertRegexp('/desktop/i', $this->client->getResponse()->getContent());
    }

    public function test_login_with_wrong_credentials_shows_an_alert()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('_submit')->form();
        $form['_username'] = 'jdoe';
        $form['_password'] = 'BadPassword';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertRegexp('/Login has failed/i', $this->client->getResponse()->getContent());
    }
    
}
