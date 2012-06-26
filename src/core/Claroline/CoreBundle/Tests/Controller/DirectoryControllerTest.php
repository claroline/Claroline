<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile as SfFile;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadDirectoryData;
use Claroline\CoreBundle\Tests\DataFixtures\Additional\LoadFileData;
use Claroline\CoreBundle\DataFixtures\LoadMimeTypeData;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;

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
        $this->upDir = $this->client->getContainer()->getParameter('claroline.files.directory');
        $this->cleanDirectory($this->upDir);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDirectory($this->upDir);
    }

    public function testUserCanCreateRootDirectory()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $rootDir = new Directory;
        $rootDir->setName('root_dir');
        $this->addResource($rootDir, $this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
        $this->client->request(
            'POST',
            "resource/node/0/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}/node.json",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($dir));
    }

    public function testUserCanCreateSubDirectory()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $rootDir = new Directory;
        $rootDir->setName('root_dir');
        $dirRi = $this->addResource($rootDir, $this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
        $subDir = new Directory;
        $subDir->setName('subDir');
        $this->addResource($subDir, $this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId(), $dirRi->getId());
        $this->client->request(
            'POST',
            "resource/node/{$dirRi->getId()}/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}/node.json",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($dir));
    }

    public function testUserCanCreateSubResource()
    {
        $ds = DIRECTORY_SEPARATOR;

        $filePath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
        $copyPath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}copy.txt";

        $this->logUser($this->getFixtureReference('user/user'));
        $rootDir = new Directory;
        $rootDir->setName('root_dir');
        $dirRi = $this->addResource($rootDir, $this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
        copy($filePath, $copyPath);
        $file = new SfFile($copyPath, 'copy.txt', null, null, null, true);
        $object = new File();
        $object->setName($file);
        $object->setShareType(1);
        $ri = $this->addResource($object, $this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId(), $dirRi->getId());
        $this->client->request(
            'POST',
            "resource/node/{$dirRi->getId()}/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}/node.json",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($dir));
    }

    public function testUserCanRemoveDirectoryAndItsContent()
    {
       $ds = DIRECTORY_SEPARATOR;

        $filePath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
        $copyPath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}copy.txt";

        $this->logUser($this->getFixtureReference('user/user'));
        $rootDir = new Directory;
        $rootDir->setName('root_dir');
        $dirRi = $this->addResource($rootDir, $this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
        copy($filePath, $copyPath);
        $file = new SfFile($copyPath, 'copy.txt', null, null, null, true);
        $object = new File();
        $object->setName($file);
        $object->setShareType(1);
        $ri = $this->addResource($object, $this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId(), $dirRi->getId());

        $this->client->request(
            'POST',
            "resource/workspace/remove/{$dirRi->getId()}/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}"
        );

        $this->assertEquals(0, count($this->getUploadedFiles($this->upDir)));
    }

    private function cleanDirectory($dir)
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

    private function getUploadedFiles()
    {
        $iterator = new \DirectoryIterator($this->upDir);
        $uploadedFiles = array();

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() !== 'placeholder') {
                $uploadedFiles[] = $file->getFilename();
            }
        }

        return $uploadedFiles;
    }

    private function addResource($object, $workspaceId, $parentId = null)
    {
        return $ri = $this
            ->client
            ->getContainer()
            ->get('claroline.resource.creator')
            ->create($object, $workspaceId, $parentId, true);
    }
}
