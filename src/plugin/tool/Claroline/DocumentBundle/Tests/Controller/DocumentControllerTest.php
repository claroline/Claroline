<?php

namespace Claroline\DocumentBundle\Tests\Controller;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\DocumentBundle\Tests\DataFixtures\LoadDirectoryData;

//TODO: test downloaded zip contain the right files 

class DocumentControllerTest extends FixtureTestCase
{
    /** @var string */
    private $uploadDirectory;

    /** @var string */
    private $stubDirectory;

    /** @var integer */
    private $rootDirId;

    protected function setUp()
    {
        parent::setUp();
        $this->uploadDirectory = $this->client
            ->getContainer()
            ->getParameter('claroline.files.directory');
        $ds = DIRECTORY_SEPARATOR;
        $this->stubDirectory = __DIR__ . "{$ds}..{$ds}stubs{$ds}";
        $this->cleanUploadDirectory();
        $this->loadFixture(new LoadDirectoryData());
        $this->rootDirId = $this->getFixtureReference("dir/dir_a")->getId();
        $this->client->followRedirects();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->cleanUploadDirectory();
    }

    public function testUploadedFilesAppearInTheDocumentList()
    {
        $this->uploadFile("{$this->stubDirectory}moveTest.txt", $this->rootDirId);
        $this->uploadFile("{$this->stubDirectory}otherMoveTest.txt", $this->rootDirId);
        
        $crawler = $this->showDirectory($this->rootDirId);
        
        $this->assertEquals(2, count($this->getUploadedFiles()));
        $this->assertEquals(2, $crawler->filter('.document_item')->count());
    }

    public function testAddedSubDirectoryAppearsInTheDirectoryContent()
    {
        $this->addSubDirectory('DIR_TEST', $this->rootDirId);
        
        $crawler = $this->showDirectory($this->rootDirId);
        
        $this->assertEquals(3, $crawler->filter('.directory_item')->count());
    }

    public function testUploadedFileCanBeDownloaded()
    {
        $this->uploadFile("{$this->stubDirectory}moveTest.txt", $this->rootDirId);
       
        $crawler = $this->showDirectory($this->rootDirId);        
        $link = $crawler->filter('.link_download_document')->eq(0)->link();
        $this->client->click($link);
        
        $headers = $this->client->getResponse()->headers;
        $this->assertTrue($headers->contains('Content-Disposition', 'attachment; filename=moveTest.txt'));
    }

    public function testUploadedFileCanBeDeleted()
    {
        $this->uploadFile("{$this->stubDirectory}moveTest.txt", $this->rootDirId);
        
        $crawler = $this->showDirectory($this->rootDirId);
        $link = $crawler->filter('.link_delete_document')->eq(0)->link();
        $this->client->click($link);
        
        $crawler = $this->showDirectory($this->rootDirId);       
        $this->assertEquals(0, $crawler->filter('.document_item')->count());
        $this->assertEquals(0, count($this->getUploadedFiles()));
    }

    public function testAddedDirectoryCanBeRemovedWithItsContent()
    {
        $this->addSubDirectory('DIR_TEST', $this->rootDirId);       
        $crawler = $this->showDirectory($this->rootDirId);    
        $link = $crawler->filter('.link_directory_show')->eq(0)->link();
        $this->client->click($link);
        $this->uploadFile("{$this->stubDirectory}moveTest.txt", $this->client->getRequest()->get('id'));
        
        $crawler = $this->showDirectory($this->rootDirId);
        $link = $crawler->filter('.link_delete_directory')->eq(0)->link();
        $this->client->click($link);
        
        $crawler = $this->showDirectory($this->rootDirId);
        $this->assertEquals(2, $crawler->filter('.directory_item')->count());
        $this->assertEquals(0, count($this->getUploadedFiles()));
    }
    
    public function testZipCanBeUploaded()
    {
        $this->uploadFile("{$this->stubDirectory}dynatree.zip", $this->rootDirId);
        
        $crawler = $this->showDirectory($this->rootDirId);
        $this->assertEquals(3, $crawler->filter('.directory_item')->count());
        
        $dynaLink = $crawler->filter('#dynatree .link_directory_show')->link();
        $crawler = $this->client->click($dynaLink);
        $this->assertEquals(4, $crawler->filter('.directory_item')->count());
        $this->assertEquals(1, $crawler->filter('.document_item')->count());
        
        $docLink = $crawler->filter('#doc .link_directory_show')->eq(0)->link();
        $crawler = $this->client->click($docLink);
        $this->assertEquals(2, $crawler->filter('.directory_item')->count());
        $this->assertEquals(63, $crawler->filter('.document_item')->count());
        $this->assertEquals(110, count($this->getUploadedFiles()));
    }
 
    public function testZipCanBeDownloaded()
    {
        $this->uploadFile("{$this->stubDirectory}dynatree.zip", $this->rootDirId);
        
        $crawler = $this->showDirectory($this->rootDirId);
        $link = $crawler->filter('.link_download_directory')->eq(2)->link();
        $crawler = $this->client->click($link);
        
        $headers = $this->client->getResponse()->headers;
        $this->assertTrue($headers->contains('Content-Disposition', 'attachment; filename=dynatree.zip'));
    }

    private function showDirectory($dirId)
    {
        return $this->client->request('GET', '/document/show/directory/' . $dirId);
    }

    private function uploadFile($filePath, $dirId)
    {
        $crawler = $this->showDirectory($dirId);
        $form = $crawler->filter('input[type=submit]')->first()->form();
        
        return $this->client->submit($form, array('Document_Form[file]' => $filePath));
    }
    
    private function addSubDirectory($name, $dirId)
    {
        $crawler = $this->showDirectory($dirId);
        $form = $crawler->filter('input[type=submit]')->last()->form();
        $crawler = $this->client->submit($form, array('Directory_Form[name]' => $name));
    }

    private function getUploadedFiles()
    {
        $iterator = new \DirectoryIterator($this->uploadDirectory);
        $uploadedFiles = array();

        foreach ($iterator as $file)
        {
            if ($file->isFile() && $file->getFilename() !== 'placeholder')
            {
                $uploadedFiles[] = $file->getFilename();
            }
        }

        return $uploadedFiles;
    }
    
    private function cleanUploadDirectory()
    {
        $iterator = new \DirectoryIterator($this->uploadDirectory);

        foreach ($iterator as $file)
        {
            if ($file->isFile() && $file->getFilename() !== 'placeholder')
            {
                chmod($file->getPathname(), 0777);
                unlink($file->getPathname());
            }
        }
    }
}