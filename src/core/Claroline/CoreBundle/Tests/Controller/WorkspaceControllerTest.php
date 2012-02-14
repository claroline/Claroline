<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CoreBundle\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadUserData;
use Claroline\CoreBundle\Tests\DataFixtures\OldLoadWorkspaceData;

// TODO : use FunctionalTestCase when active
class WorkspaceControllerTest extends TransactionalTestCase
{
    /**@var Crawler */
    private $crawler;

    protected function setUp()
    {
        parent::setUp();
        $this->markTestSkipped();
        $this->prepareClient();
        $this->loadFixtures();
        $this->logIn();
    }

    protected function prepareClient()
    {
        $this->client->followRedirects();
    }

    protected function loadFixtures()
    {
        $em = $this->client->getContainer()->get('doctrine')->getEntityManager();
        $refRepo = new ReferenceRepository($em);

        $userFixture = new LoadUserData();
        $userFixture->setContainer($this->client->getContainer());
        $userFixture->setReferenceRepository($refRepo);
        $userFixture->load($em);

        $wsFixture = new LoadWorkspaceData();
        $wsFixture->setContainer($this->client->getContainer());
        $wsFixture->setReferenceRepository($refRepo);
        $wsFixture->load($em);
    }    

    protected function logIn($username = 'admin', $password = 'USA')
    {
        $this->client->request('GET', '/logout');
        $this->crawler = $this->client->request('GET', '/login');
        $form = $this->crawler->filter('input[id=_submit]')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $this->client->submit($form);
    }

    protected function goToDesktopAndAssertNumberOfListedWorkspaces($number)
    {
        $this->crawler = $this->client->request('GET', '/desktop');
        
        $this->assertEquals($number, $this->crawler->filter('#content #workspaces li')->count());
    }

    public function testRemovalOfAWorkspaceByTheOwner()
    {
        $this->goToDesktopAndAssertNumberOfListedWorkspaces(10);

        
        $deleteForm = $this->crawler->filter('#claro_desktop_content #workspaces li form input')->first()->form();
        $this->client->submit($deleteForm);
        $this->goToDesktopAndAssertNumberOfListedWorkspaces(9);
    }
 
    public function testRemovalOfAWorkspaceBySomeoneElse()
    {
        $this->logIn('jdoe', 'topsecret');
        $this->goToDesktopAndAssertNumberOfListedWorkspaces(10);

        $this->crawler = $this->client->request('GET', '/desktop');
        $deleteForm = $this->crawler->filter('#claro_desktop_content #workspaces li form input')->first()->form();
        $this->client->submit($deleteForm);
        
        // test 403 status code
        $this->assertRegExp('/Access Denied/', $this->client->getResponse()->getContent());
    }

    public function testCreationOfAWorkspace()
    {
        $this->goToDesktopAndAssertNumberOfListedWorkspaces(10);

        $this->crawler = $this->client->request('GET', '/workspaces/new');
        $form = $this->crawler->filter('input[type=submit]')->form();
        $form['workspace_form[name]'] = 'Workspace test';
        $this->client->submit($form);
        
        $this->goToDesktopAndAssertNumberOfListedWorkspaces(11);
    }
    
    public function testCreatorOfWSCanDeleteIt()
    {
        $this->logIn('jdoe', 'topsecret');
        $this->crawler = $this->client->request('GET', '/workspaces/new');
        $form = $this->crawler->filter('input[type=submit]')->form();
        $form['workspace_form[name]'] = 'Workspace test';
        $this->client->submit($form);
        
        $this->goToDesktopAndAssertNumberOfListedWorkspaces(11);
        
        $deleteForm = $this->crawler->filter('#claro_desktop_content #workspaces li form input')->eq(10)->form();
        $this->client->submit($deleteForm);
          
        $this->goToDesktopAndAssertNumberOfListedWorkspaces(10);
    }
}