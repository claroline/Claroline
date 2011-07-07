<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\UserBundle\Tests\DataFixtures\ORM\LoadUserData;
use Claroline\CoreBundle\Tests\DataFixtures\ORM\LoadWorkspaceData;
use Symfony\Bundle\FrameworkBundle\Client;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class WorkspaceControllerTest extends WebTestCase
{
    /**@var Client */
    private $client;

    protected function setUp()
    {
        $this->prepareClient();
        $this->load_fixtures();
        $this->log_in();
    }

    protected function load_fixtures()
    {
        $user_fixture = new LoadUserData();
        $user_fixture->setContainer($this->client->getContainer());

        $workspace_fixture =  new LoadWorkspaceData();

        $loader = new Loader();
        $loader->addFixture($user_fixture);
        $loader->addFixture($workspace_fixture);

        $purger = new ORMPurger();
        $em = $this->client->getContainer()->get('doctrine')->getEntityManager();
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures());
    }

    protected function prepareClient()
    {
        $this->client = self :: createClient();
        $this->client->followRedirects();
    }

    protected function log_in()
    {
        //$this->client->request('POST', '/login_check', array('_username' => 'jdoe', '_password' => 'topsecret'));
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('input[name=_submit]')->form();
        $form['_username'] = 'jdoe';
        $form['_password'] = 'topsecret';
        $this->client->submit($form);
    }


    public function test_removal_of_a_workspace()
    {
        $crawler = $this->client->request('GET', '/desktop');
        $this->assertEquals(10, $crawler->filter('#content #workspaces li')->count());

        $deleteForm = $crawler->filter('#content #workspaces li input')->first()->form();
        $this->client->submit($deleteForm);

        // do not forget to refresh the crawler or the assert will be done on the previous page
        $crawler = $this->client->getCrawler();
        $this->assertEquals(9, $crawler->filter('#content #workspaces li')->count());
    }
}