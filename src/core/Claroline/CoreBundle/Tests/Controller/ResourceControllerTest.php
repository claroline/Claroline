<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;

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
}    
    