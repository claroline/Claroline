<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class WorkspaceControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadWorkspaceFixture();
        $this->client->followRedirects();
        $this->markTestSkipped('fixture import is bugging');
    }
    
    
    public function testWSCreatorCanSeeHisWS()
    {
         $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
         $link = $crawler->filter('#link_workspace')->link();
         $crawler = $this->client->click($link);
         $link = $crawler->filter('#link_owned_WS')->link();
         $crawler = $this->client->click($link);   
         $this->assertEquals(4, $crawler->filter('.row_workspace')->count()); 
    }
    
    public function testAdminCanSeeHisWs()
    {
         $crawler = $this->logUser($this->getFixtureReference('user/admin'));
         $link = $crawler->filter('#link_workspace')->link();
         $crawler = $this->client->click($link);
         $link = $crawler->filter('#link_owned_WS')->link();
         $this->assertEquals(2, $crawler->filter('.row_workspace')->count());
    }
    
    public function testWSCreatorCanCreateWS()
    {
         $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
         $link = $crawler->filter('#link_workspace')->link();
         $crawler = $this->client->click($link);
         $link = $crawler->filter('#link_create_WS_form')->link();
         $crawler = $this->client->click($link); 
         $form = $crawler->filter('input[type=submit]')->form(); 
         $form['workspace_form[name]'] = 'new_workspace';
         $form['workspace_form[type]'] = 'simple';
         $this->client->submit($form);
         $crawler = $this->client->request('GET', "/workspace/list/{$this->getFixtureReference('user/ws_creator')->getId()}");
         $this->assertEquals(5, $crawler->filter('.row_workspace')->count()); 
    }
    
    public function testWSCreatorCanDeleteHisWS()
    {
         $this->logUser($this->getFixtureReference('user/ws_creator'));  
         $crawler = $this->client->request('GET', "/workspace/list/{$this->getFixtureReference('user/ws_creator')->getId()}");
         $link = $crawler->filter("#link_delete_{$this->getFixtureReference('workspace/ws_d')->getId()}")->link();
         $crawler = $this->client->click($link);
         $crawler = $this->client->request('GET', "/workspace/list/{$this->getFixtureReference('user/ws_creator')->getId()}");
         $this->assertEquals(3, $crawler->filter('.row_workspace')->count());          
    }
    
    public function testWSManagerCanSeeHisWS()
    {
         $this->logUser($this->getFixtureReference('user/ws_creator'));  
         $crawler = $this->client->request('GET', "/workspace/list/{$this->getFixtureReference('user/ws_creator')->getId()}");
         $link = $crawler->filter("#link_show_{$this->getFixtureReference('workspace/ws_d')->getId()}")->link();
         $crawler = $this->client->click($link);
         $this->assertEquals(1, $crawler->filter("#div_WS_show")->count());
    }
    
    public function testUserCanSeeWSList()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/workspace/list");
        $this->assertEquals(6, $crawler->filter('.row_workspace')->count()); 
    } 
}