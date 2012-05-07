<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadWorkspaceData;

class ResourceControllerTest extends FunctionalTestCase
{
    /** @var string */
    private $filePath;
    
    public function setUp()
    {
        parent::setUp();       
        $this->loadUserFixture();
        $this->loadFixture(new LoadResourceTypeData());
        $this->client->followRedirects();
        $ds = DIRECTORY_SEPARATOR; 
        $this->filePath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
    }
    
    //this test works with the file controller
    public function testUserCanCreateFileResource()
    {
        //test
        $this->logUser($this->getFixtureReference('user/user'));
        $this->addRootFile($this->filePath);
        $crawler = $this->client->request('GET', '/resource/directory/null');
        $this->assertEquals($crawler->filter('.row_resource')->count(), 1);        
    }  
    
    public function testResourceDefaultActionIsProtected()
    {
       $this->logUser($this->getFixtureReference('user/user'));
       $id = $this->addRootFile($this->filePath);
       $this->logUser($this->getFixtureReference('user/user_2'));
       $this->client->request('GET', "/resource/click/file/{$id}");
       $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testCreatorCanAccessResourceDefaultAction()
    {
       $this->logUser($this->getFixtureReference('user/user'));
       $id = $this->addRootFile($this->filePath);
       $this->client->request('GET', "/resource/click/file/{$id}");
       $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
    
    public function testResourceOpenActionIsProtected()
    {
       $this->logUser($this->getFixtureReference('user/user'));
       $id = $this->addRootFile($this->filePath);
       $this->logUser($this->getFixtureReference('user/user_2'));
       $this->client->request('GET', "/resource/open/{$id}");
       $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testCreatorCanAccessResourceOpenAction()
    {
       $this->logUser($this->getFixtureReference('user/user'));
       $id = $this->addRootFile($this->filePath);
       $this->client->request('GET', "/resource/open/{$id}");
       $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
            
    public function testResourceDeleteActionIsProtected()
    {
       $this->logUser($this->getFixtureReference('user/user'));
       $id = $this->addRootFile($this->filePath);
       $this->logUser($this->getFixtureReference('user/user_2'));
       $this->client->request('GET', "/resource/delete/{$id}");
       $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testCreatorCanAccessDeleteAction()
    {
       $this->logUser($this->getFixtureReference('user/user'));
       $id = $this->addRootFile($this->filePath);
       $this->client->request('GET', "/resource/delete/{$id}");
       $this->assertEquals(200, $this->client->getResponse()->getStatusCode()); 
    }
    
    
    public function testResourceCanBeAddedToWorkspace()
    {
       $this->loadFixture(new LoadWorkspaceData());
       $this->logUser($this->getFixtureReference('user/user'));
       $this->initWorkspacesTests();
       $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
       $this->assertEquals(1, $crawler->filter('.row_resource')->count()); 
    }
    
    public function testRegisterUserHasAccessToWorkspaceResources()
    {
       $this->loadFixture(new LoadWorkspaceData());
       $this->logUser($this->getFixtureReference('user/user'));
       $this->initWorkspacesTests();
       $this->logUser($this->getFixtureReference('user/user_2'));
       $this->registerToWorkspaceA();
       $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
       $link = $crawler->filter('.link_resource_view')->first()->link();
       $this->client->click($link);
       $this->assertEquals(1, $crawler->filter('.row_resource')->count()); 
       $link = $crawler->filter('.link_resource_view')->first()->link();
       $this->client->click($link);
       $this->assertEquals(1, $crawler->filter('.row_resource')->count()); 
    }
    
    public function testUnregisteredUserLostAccessToWorkspaceResources()
    {
       $this->loadFixture(new LoadWorkspaceData());
       $this->logUser($this->getFixtureReference('user/user'));
       $rootId = $this->initWorkspacesTests();
       $this->logUser($this->getFixtureReference('user/user_2'));
       $this->registerToWorkspaceA();
       $this->unregisterFromWorkspaceA();
       $this->client->request('GET', "/resource/click/directory/{$rootId}");
       $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    private function initWorkspacesTests()
    {
       $rootId = $this->createResourcesTree();
       $this->registerToWorkspaceA();
       $crawler = $this->client->request('GET', '/workspace/list');
       $id = $crawler->filter(".row_workspace")->first()->attr('data-workspace_id');
       $link =  $crawler->filter("#link_show_{$id}")->link();
       $this->client->click($link);
       //add root to workspace
       $this->client->request('GET', "/resource/workspace/add/{$rootId}/{$this->getFixtureReference('workspace/ws_a')->getId()}");
       
       return $rootId;
    }
        
    private function addRootFile($filePath)
    {
        $crawler = $this->client->request('GET', '/desktop');
        $link = $crawler->filter('#resource_manager_link')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('input[type=submit]')->form(); 
        $fileTypeId = $this->getFixtureReference('resource_type/file')->getId();
        $crawler = $this->client->submit($form, array('select_resource_form[type]' => $fileTypeId));
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('file_form[name]' => $filePath));
        $id = $crawler->filter(".row_resource")->last()->attr('data-resource_id');

        return $id;
    }
    
     private function addRootDirectory($name)
     {
        $crawler = $this->client->request('GET', '/resource/directory/null');
        $form = $crawler->filter('input[type=submit]')->form(); 
        $fileTypeId = $this->getFixtureReference('resource_type/directory')->getId();
        $crawler = $this->client->submit($form, array('select_resource_form[type]' => $fileTypeId));
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('directory_form[name]' => $name));
        $id = $crawler->filter(".row_resource")->last()->attr('data-resource_id');
        
        return $id;
     }
     
     private function addFileInCurrentDirectory($filePath, $crawler)
     {
        $form = $crawler->filter('input[type=submit]')->form(); 
        $fileTypeId = $this->getFixtureReference('resource_type/file')->getId();
        $crawler = $this->client->submit($form, array('select_resource_form[type]' => $fileTypeId));
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('file_form[name]' => $filePath));
        $crawler->filter(".row_resource")->last()->attr('data-resource_id');
     }
     
     private function createResourcesTree()
     {
        $id = $this->addRootDirectory('ROOT_DIRECTORY_user');
        $crawler = $this->client->request('GET', '/resource/directory/null');
        $link = $crawler->filter("#link_resource_{$id}")->link();
        $crawler = $this->client->click($link);
        $this->addFileInCurrentDirectory($this->filePath, $crawler);
        
        return $id;
     }
     
     private function registerToWorkspaceA()
     {
         $crawler = $this->client->request('GET', '/workspace/list');
         $link = $crawler->filter("#link_registration_{$this->getFixtureReference('workspace/ws_a')->getId()}")->link();
         $this->client->click($link);
     } 
     
     private function unregisterFromWorkspaceA()
     {
         $crawler = $this->client->request('GET', '/workspace/list');
         $link = $crawler->filter("#link_unregistration_{$this->getFixtureReference('workspace/ws_a')->getId()}")->link();
         $this->client->click($link);
     }
}    
    