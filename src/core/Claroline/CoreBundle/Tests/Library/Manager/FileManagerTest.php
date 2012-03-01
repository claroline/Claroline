<?php
namespace Claroline\CoreBundle\Tests\Library\Manager;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Claroline\CoreBundle\Library\Manager\FileManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;

class FileManagerTest extends FunctionalTestCase
{
    /** @var Claroline\CoreBundle\Manager\FileManager\ */
    private $manager;
    
    /** @var string */   
    private $upDir;
        
    /** @var string */   
    private $stubDir;
    
    /** @var EntityManager */
    protected $em;
  
    protected function setUp()
    {
        parent::setUp();
        
        $ds = DIRECTORY_SEPARATOR;
        $this->upDir  = $this->client->getContainer()->getParameter('claroline.files.directory');
        $this->stubDir = __DIR__ . "{$ds}..{$ds}..{$ds}stubs{$ds}";
        $this->manager =  $this->client->getContainer()->get('claroline.files.file_manager');
        $this->cleanDirectory($this->upDir);
        $this->cleanDirectory(__DIR__ . "{$ds}..{$ds}..{$ds}stubs");
        $this->setUpCopy();
    }
    
    protected function tearDown()
    {
        parent::tearDown();
        
        $ds = DIRECTORY_SEPARATOR;
        $this->cleanDirectory($this->upDir);
        $this->cleanDirectory(__DIR__ . "{$ds}..{$ds}..{$ds}stubs");
    }     
    
    public function testUploadFile()
    {  
        $this->uploadFile('new_1.txt');
        $this->uploadFile('new_2.txt');
        $instances = $this->manager->findAll();
        $this->assertEquals(2, count($instances));
        $this->assertEquals(2, count($this->getUploadedFiles()));
    }
    
    public function testDeleteFile()
    {
        $this->uploadFile('new_1.txt');
        $this->uploadFile('new_2.txt');
        $files=$this->manager->findAll();
        $this->manager->deleteById($files[0]->getId());
        $instances = $this->manager->findAll();
        $this->assertEquals(1, count($instances));
        $this->assertEquals(1, count($this->getUploadedFiles()));
    }
   
    public function testDownloadFile()
    {
        $this->uploadFile('new_1.txt');
        $this->uploadFile('new_2.txt');
        $files = $this->manager->findAll();
        $response = $this->manager->setDownloadResponseById($files[0]->getId(), new Response());
        $this->assertTrue($response->headers->contains('Content-Disposition', 'attachment; filename=new_1.txt'));
    }
    
    private function uploadFile($fileName)
    {
       $filePath = $this->stubDir.$fileName;
       $file = new File($filePath);
       $this->manager->upload($file, $fileName); 
    }
    
    private function setUpCopy()
    {
        //these files must be copied before being moved 
        //and supressed at the end of the test. It happens because
        //Symfony2 forms use temporary files.
        $originalPath = $this->stubDir.'originalFile.txt';
        $firstNewPath = $this->stubDir.'new_1.txt';
        $secNewPath = $this->stubDir.'new_2.txt'; 
        copy($originalPath, $firstNewPath);
        copy($originalPath, $secNewPath);
        
    }
    
    private function getUploadedFiles()
    {
        $iterator = new \DirectoryIterator($this->upDir);
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
    
    
}