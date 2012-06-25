<?php

namespace Claroline\CoreBundle\Tests\DataFixtures\Additional;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Resource\File;

class LoadFileData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface  */
    protected $container;

    /** @var string */
    protected $stubDir;

    /** @var string */
    protected $upDir;

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
        $this->addFiles($this->getReference("user/user"), $manager);
    }

    protected function setLoader()
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->stubDir = __DIR__ . "{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}";
        $this->upDir = $this->getContainer()->getParameter('claroline.files.directory');
        $this->cleanDirectory($this->upDir);
    }

    protected function addFiles($user, $manager)
    {
        $this->createFile($user, null, $manager);
        $this->createFile($user, $this->getReference("directory/DIR_ROOT_{$user->getUsername()}"), $manager);
    }

    protected function createFile($user, $dir, $manager)
    {
        $filePath = $this->stubDir . "file.txt";
        $size = 1000;
        $hashName = $this->GUID();

        $file = new File();
        $file->setSize($size);
        $file->setName("test.txt");
        $file->setHashName($hashName);
        $file->setCreator($user);
        $file->setParent($dir);
        $file->setResourceType($this->getReference('resource_type/file'));
        $manager->persist($file);
        $manager->flush();
        copy($filePath, $this->upDir . DIRECTORY_SEPARATOR . $hashName);
    }

    protected function cleanDirectory($dir)
    {

        $iterator = new \DirectoryIterator($dir);

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() !== 'placeholder'
                && $file->getFilename() !== 'originalFile.txt'
                && $file->getFilename() !== 'originalZip.zip'
            ) {
                chmod($file->getPathname(), 0777);
                unlink($file->getPathname());
            }
        }
    }

    private function GUID()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)
        );
    }

    public function getOrder()
    {
        return 10;
    }
}