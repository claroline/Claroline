<?php

namespace Claroline\WorkspaceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\UserBundle\Tests\DataFixtures\ORM\LoadUserData;
use Claroline\WorkspaceBundle\Tests\DataFixtures\ORM\LoadWorkspaceData;

class WorkspaceControllerTest extends WebTestCase
{
    /**@var Client */
    private $client;

    /**@var Crawler */
    private $crawler;


    protected function setUp()
    {
        $this->prepareClient();
        $this->load_fixtures();
        $this->log_in();
    }

    protected function prepareClient()
    {
        $this->client = self :: createClient();
        $this->client->followRedirects();
        $this->client->beginTransaction();
    }

    protected function load_fixtures()
    {
        $em = $this->client->getContainer()->get('doctrine')->getEntityManager();
        $ref_repo = new ReferenceRepository($em);

        $user_fixture = new LoadUserData();
        $user_fixture->setContainer($this->client->getContainer());
        $user_fixture->setReferenceRepository($ref_repo);
        $user_fixture->load($em);

        $ws_fixture = new LoadWorkspaceData();
        $ws_fixture->setContainer($this->client->getContainer());
        $ws_fixture->setReferenceRepository($ref_repo);
        $ws_fixture->load($em);
    }    

    protected function log_in($username = 'admin', $password = 'USA')
    {
        $this->client->request('GET', '/logout');
        $this->crawler = $this->client->request('GET', '/login');
        $form = $this->crawler->filter('input[id=_submit]')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $this->client->submit($form);
    }

    protected function tearDown()
    {
        $this->client->rollback();
    }

    protected function go_to_desktop_and_assert_number_of_listed_workspaces($number)
    {
        $this->crawler = $this->client->request('GET', '/desktop');
        $this->assertEquals($number, $this->crawler->filter('#content #workspaces li')->count());
    }


    public function test_removal_of_a_workspace_by_the_owner()
    {
        $this->go_to_desktop_and_assert_number_of_listed_workspaces(10);

        $this->crawler = $this->client->request('GET', '/desktop');
        $deleteForm = $this->crawler->filter('#content #workspaces li input')->first()->form();
        $this->client->submit($deleteForm);

        $this->go_to_desktop_and_assert_number_of_listed_workspaces(9);
    }

    public function test_removal_of_a_workspace_by_someone_else()
    {
        $this->log_in('jdoe', 'topsecret');
        $this->go_to_desktop_and_assert_number_of_listed_workspaces(10);

        $this->crawler = $this->client->request('GET', '/desktop');
        $deleteForm = $this->crawler->filter('#content #workspaces li input')->first()->form();
        $this->client->submit($deleteForm);

        $this->client->getContainer()->get('logger')->debug($this->client->getContainer()->get('security.context')->getToken()->getUser()->getUsername());
        $this->client->getContainer()->get('logger')->debug($this->client->getResponse()->getContent());
        $this->assertRegExp('/Access Denied/', $this->client->getResponse()->getContent());
    }

    public function test_creation_of_a_workspace()
    {
        $this->go_to_desktop_and_assert_number_of_listed_workspaces(10);

        $this->crawler = $this->client->request('GET', '/workspaces/new');
        $form = $this->crawler->filter('input[type=submit]')->form();
        $form['workspace_form[name]'] = 'Workspace test';
        $this->client->submit($form);

        $this->go_to_desktop_and_assert_number_of_listed_workspaces(11);
    }
}