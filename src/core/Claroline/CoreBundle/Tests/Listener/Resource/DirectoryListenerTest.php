<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\Resource\Directory;
use DirectoryIterator;

class DirectoryListenerTest extends FunctionalTestCase
{
    /** @var string */
    private $upDir;

    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user'));
        $this->client->followRedirects();
        $this->upDir = $this->client->getContainer()->getParameter('claroline.files.directory');
        $this->cleanDirectory($this->upDir);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDirectory($this->upDir);
    }

    public function testUserCanCreateDirectory()
    {
        $this->logUser($this->getUser('user'));
        $rootDir = new Directory;
        $rootDir->setName('root_dir');
        $this->client
            ->getContainer()
            ->get('claroline.resource.manager')
            ->create($rootDir, $this->getDirectory('user')->getId(), 'directory');
        $this->client->request('GET', "/resource/directory/{$this->getDirectory('user')->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(1, count($dir->resources));
    }

    public function testUserCanRemoveDirectoryAndItsContent()
    {
        $this->loadDirectoryData('user', array('user/dir1/dir2'));
        $this->loadFileData('user', 'dir2', array('foo.txt'));
        $dirRi = $this->getDirectory('dir1');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/delete?ids[]={$dirRi->getId()}");
        $this->client->request('GET', "/resource/directory/{$this->getDirectory('user')->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(0, count($dir->resources));
    }

    private function cleanDirectory($dir)
    {
        $iterator = new DirectoryIterator($dir);

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
}
