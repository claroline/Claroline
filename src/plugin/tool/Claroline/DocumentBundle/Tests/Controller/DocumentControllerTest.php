<?php

namespace Claroline\DocumentBundle\Tests\Controller;

use Claroline\CoreBundle\Testing\TransactionalTestCase;

class DocumentControllerTest extends TransactionalTestCase
{
    /** @var string */
    private $uploadDirectory;

    /** @var string */
    private $stubDirectory;
    
    protected function setUp()
    {
        parent::setUp();
        $this->uploadDirectory = $this->client
            ->getContainer()
            ->getParameter('claroline.files.directory');
        $ds = DIRECTORY_SEPARATOR;
        $this->stubDirectory = __DIR__ . "{$ds}..{$ds}stubs{$ds}";
        $this->cleanUploadDirectory();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->cleanUploadDirectory();
    }

    public function testUploadedFilesAppearInTheDocumentList()
    {
        $this->uploadFile("{$this->stubDirectory}moveTest.txt");
        $this->uploadFile("{$this->stubDirectory}otherMoveTest.txt");

        $crawler = $this->client->request('GET', '/document/list');
        $this->assertEquals(2, $crawler->filter('.document_item')->count());
        $this->assertEquals(2, count($this->getUploadedFiles()));
    }
    
    public function testUploadedFileCanBeDownloaded()
    {
        $this->uploadFile("{$this->stubDirectory}moveTest.txt");
        
        $crawler = $this->client->request('GET', '/document/list');
        $link = $crawler->filter('.link_download')->eq(0)->link();
        $crawler = $this->client->click($link);
        
        $this->assertTrue($this->client->getResponse()->headers->contains(
            'Content-Disposition', 'attachment; filename=moveTest.txt')
        );     
        $this->assertEquals(
            file_get_contents("{$this->stubDirectory}moveTest.txt"), 
            $this->client->getResponse()->getContent()
        );
    }
    
    public function testUploadedFileCanBeDeleted()
    {
        $this->uploadFile("{$this->stubDirectory}moveTest.txt");
        
        $crawler = $this->client->request('GET', '/document/list');
        $link = $crawler->filter('.link_delete')->eq(0)->link();
        $this->client->click($link);
        
        $crawler = $this->client->request('GET', '/document/list');
        $this->assertEquals(0, $crawler->filter('.document_item')->count());
        $this->assertEquals(0, count($this->getUploadedFiles()));
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
  
    private function uploadFile($filePath)
    {
        $crawler = $this->client->request('GET', '/document/form');
        $form = $crawler->filter('input[type=submit]')->form();
        $this->client->submit($form, array('Document_Form[file]' => $filePath));
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
}