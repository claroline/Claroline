<?php
namespace Claroline\CoreBundle\Tests\Library\Manager;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Claroline\CoreBundle\Library\Manager\FileManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;

class FileManagerTest extends FixtureTestCase
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
        
        $this->loadUserFixture();  
        $this->loadFixture(new LoadResourceTypeData());
        $ds = DIRECTORY_SEPARATOR;
        $this->upDir  = $this->client->getContainer()->getParameter('claroline.files.directory');
        $this->stubDir = __DIR__ . "{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}";
        $this->manager =  $this->client->getContainer()->get('claroline.file.manager');
        $this->cleanDirectory($this->upDir);
        $this->cleanDirectory(__DIR__ . "{$ds}..{$ds}..{$ds}Stub");
        $this->setUpCopy();
    }
    
    protected function tearDown()
    {
        parent::tearDown();
        
        $ds = DIRECTORY_SEPARATOR;
        $this->cleanDirectory($this->upDir);
        $this->cleanDirectory(__DIR__ . "{$ds}..{$ds}..{$ds}Stub{$ds}");
    }     
    
    public function testUploadFile()
    {  
        $this->uploadFile('new_1.txt', $this->getFixtureReference('user/admin'));
        $this->uploadFile('new_2.txt', $this->getFixtureReference('user/admin'));
        $instances = $this->manager->findAll();
        $this->assertEquals(2, count($instances));
        $this->assertEquals(2, count($this->getUploadedFiles()));
    }
    
    public function testDeleteFile()
    {
        $this->uploadFile('new_1.txt', $this->getFixtureReference('user/admin'));
        $this->uploadFile('new_2.txt', $this->getFixtureReference('user/admin'));
        $files=$this->manager->findAll();
        $this->manager->deleteById($files[0]->getId());
        $instances = $this->manager->findAll();
        $this->assertEquals(1, count($instances));
        $this->assertEquals(1, count($this->getUploadedFiles()));
    }
   
    public function testDownloadFile()
    {
        $this->uploadFile('new_1.txt', $this->getFixtureReference('user/admin'));
        $this->uploadFile('new_2.txt', $this->getFixtureReference('user/admin'));
        $files = $this->manager->findAll();
        $response = $this->manager->setDownloadResponseById($files[0]->getId(), new Response());
        $this->assertTrue($response->headers->contains('Content-Disposition', 'attachment; filename=new_1.txt'));
    }
    
    private function uploadFile($fileName, $user)
    {
       $filePath = $this->stubDir.$fileName;
       $file = new File($filePath);
       $this->manager->upload($file, $fileName, $user, null); 
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