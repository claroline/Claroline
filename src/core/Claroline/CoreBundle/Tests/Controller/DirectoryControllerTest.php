<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use DirectoryIterator;

class DirectoryControllerTest extends FunctionalTestCase
{
    /** @var string */
    private $upDir;

    /** @var $ResourceInstance */
    private $pwr;

    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('user'));
        $this->client->followRedirects();
        $this->upDir = $this->client->getContainer()->getParameter('claroline.files.directory');
        $this->cleanDirectory($this->upDir);
        $this->pwr = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findWorkspaceRoot($this->getFixtureReference('user/user')->getPersonalWorkspace());
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDirectory($this->upDir);
    }

    public function testUserCanCreateDirectory()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $rootDir = new Directory;
        $rootDir->setName('root_dir');
        $this->addResource($rootDir, $this->pwr->getId(), 'directory');
        $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(1, count($dir->resources));
    }

    public function testUserCanCreateSubResource()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $rootDir = new Directory;
        $rootDir->setName('root_dir');
        $dirRi = $this->addResource($rootDir, $this->pwr->getId(), 'directory');
        $object = new File();
        $object->setName('file.txt');
        $object->setSize(42);
        $object->setMimeType('Mime/Type');
        $object->setHashName('hashName');
        $this->addResource($object, $dirRi->getId(), 'file');
        $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(1, count($dir->resources));
    }

    public function testUserCanRemoveDirectoryAndItsContent()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $rootDir = new Directory;
        $rootDir->setName('root_dir');
        $dirRi = $this->addResource($rootDir, $this->pwr->getId(), 'directory');
        $object = new Directory();
        $object->setName('child_dir');
        $this->addResource($object, $dirRi->getId(), 'directory');
        $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $response);
        $this->assertEquals(1, count($response->resources));
        $this->client->request('GET', "/resource/delete?ids[]={$dirRi->getId()}");
        $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
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

    private function addResource($object, $parentId, $resourceType)
    {
        return $this->client
            ->getContainer()
            ->get('claroline.resource.manager')
            ->create($object, $parentId, $resourceType);
    }
}
