<?php
namespace Claroline\CoreBundle\Tests\DataFixtures\Additional;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class LoadFileData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface  */
    protected $container;
    
    /** @var string */   
    protected $stubDir;
    
    /** @var string */
    protected $upDir;
    
    /** @var FileManager */
    protected $fileManager;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function getContainer()
    {
        return $this->container;
    }
    
    public function load(ObjectManager $manager)
    {
       $this->setLoader(); 
       $this->addFiles($this->getReference("user/user"));
    }
    
    protected function setLoader()
    {       
        $ds = DIRECTORY_SEPARATOR;
        $this->stubDir = __DIR__ . "{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}";
        $this->fileManager = $this->getContainer()->get('claroline.file.manager');
        $this->upDir  = $this->getContainer()->getParameter('claroline.files.directory');
        $this->cleanDirectory($this->upDir);
    }
    
    protected function addFiles($user)
    {
        $this->createFile($user, null);
        $this->createFile($user, $this->getReference("directory/DIR_ROOT_{$user->getUsername()}"));
    }
    
    protected function createFile($user, $dir)
    {
        $originalPath = $this->stubDir.'originalFile.txt';
        $filePath = $this->stubDir."file.txt";
        copy($originalPath, $filePath);
        $file = new File($filePath);
        $this->fileManager->upload($file, "file.txt", $user, $dir);
    }
    
    protected function cleanDirectory($dir)
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
    
    public function getOrder()
    {
        return 10;
    }   
}