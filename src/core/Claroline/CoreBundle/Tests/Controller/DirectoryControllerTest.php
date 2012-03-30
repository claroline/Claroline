<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadDirectoryData;
use Claroline\CoreBundle\Tests\DataFixtures\Additional\LoadFileData;

class DirectoryControllerTest extends FunctionalTestCase
{
   /** @var string */   
    private $upDir;
    
    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadFixture(new LoadResourceTypeData());     
        $this->client->followRedirects();
        $this->upDir  = $this->client->getContainer()->getParameter('claroline.files.directory'); 
        $this->cleanDirectory($this->upDir);
    }
    
    public function testUserCanCreateRootDirectory()
    {
        $this->logUser($this->getFixtureReference('user/admin'));   
        $crawler = $this->client->request('GET', '/resource/index');
        $form = $crawler->filter('input[type=submit]')->form(); 
        $form['choose_resource_form[type]'] = $this->getFixtureReference('resource_type/directory')->getId();
        $crawler = $this->client->submit($form);
        $form = $crawler->filter('input[type=submit]')->form();
        $this->client->submit($form, array('directory_form[name]' => 'abc'));
        $crawler = $this->client->request('GET', '/resource/index');
        $this->assertEquals(1, count($crawler->filter(".row_resource")));
    }
    
    public function testUserCanCreateSubDirectory()
    {
        $this->loadFixture(new LoadDirectoryData());
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/directory/view/{$this->getFixtureReference('directory/DIR_ROOT_user')->getId()}");       
        $form = $crawler->filter('input[type=submit]')->form();
        $dirTypeId = $this->getFixtureReference('resource_type/directory')->getId(); 
        $crawler = $this->client->submit($form, array('choose_resource_form[type]' => $dirTypeId));
        $form = $crawler->filter('input[type=submit]')->form();
        $this->client->submit($form, array('directory_form[name]' => 'abc'));
        $crawler = $this->client->request('GET', "/directory/view/{$this->getFixtureReference('directory/DIR_ROOT_user')->getId()}");
        $this->assertEquals(3, count($crawler->filter(".row_resource")));
    }
    
    public function testUserCanCreateSubResource()
    {
        $this->loadFixture(new LoadDirectoryData());
        $ds = DIRECTORY_SEPARATOR; 
        $filePath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
                
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/directory/view/{$this->getFixtureReference('directory/DIR_ROOT_user')->getId()}");
        $form = $crawler->filter('input[type=submit]')->form(); 
        $form['choose_resource_form[type]'] = $this->getFixtureReference('resource_type/file')->getId();
        $crawler = $this->client->submit($form);
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('File_Form[file]' => $filePath));
        $crawler = $this->client->request('GET', "/directory/view/{$this->getFixtureReference('directory/DIR_ROOT_user')->getId()}");
        $this->assertEquals(3, count($crawler->filter(".row_resource")));
    }
     
    public function testUserCanRemoveDirectoryAndItsContent()
    {
        $this->loadFixture(new LoadDirectoryData());
        $this->loadFixture(new LoadFileData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertEquals(2, count($this->getUploadedFiles($this->upDir)));
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', '/resource/index');
        $link = $crawler->filter("#link_resource_delete_{$this->getFixtureReference('directory/DIR_ROOT_user')->getId()}")->link();
        $this->client->click($link);
        $crawler = $this->client->request('GET', '/resource/index');
        $this->assertEquals(1, count($this->getUploadedFiles($this->upDir)));
        $this->assertEquals(1, count($crawler->filter(".row_resource"))); 
    }
      
    private function cleanDirectory($dir)
    {    
        $iterator = new \DirectoryIterator($dir);

        foreach ($iterator as $file)
        {
            if ($file->isFile() && $file->getFilename() !== 'placeholder'
                    && $file->getFilename() !== 'originalFile.txt'
                    && $file->getFilename() !== 'originalZip.zip'
               )
            {
                chmod($file->getPathname(), 0777);
                unlink($file->getPathname());
            }
        }
    }
    
     private function getUploadedFiles()
     {
        $iterator = new \DirectoryIterator($this->upDir);
        $uploadedFiles = array();

        foreach($iterator as $file)
        {
            if ($file->isFile() && $file->getFilename() !== 'placeholder')
            {
                $uploadedFiles[] = $file->getFilename();
            }
        }

        return $uploadedFiles;
     }
}
