<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadWorkspaceData;
use Claroline\CoreBundle\DataFixtures\LoadMimeTypeData;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;

class ResourceControllerTest extends FunctionalTestCase
{
    private $resourceInstanceRepository;
    private $upDir;
    private $pwr;
    private $userRoot;

    public function setUp()
    {
        parent::setUp();
        $this->loadFixture(new LoadResourceTypeData());
        $this->loadUserFixture();
        $this->loadFixture(new LoadWorkspaceData());
        $this->client->followRedirects();
        $ds = DIRECTORY_SEPARATOR;
        $this->originalPath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
        $this->copyPath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}copy.txt";
        $this->upDir = $this->client->getContainer()->getParameter('claroline.files.directory');
        $this->resourceInstanceRepository = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance');

        $this->pwr = $this->resourceInstanceRepository->getWSListableRootResource($this->getFixtureReference('user/user')->getPersonalWorkspace());
        $this->userRoot = $this->resourceInstanceRepository->getWSListableRootResource($this->getFixtureReference('workspace/ws_a'));
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->cleanDirectory($this->upDir);
    }

    public function testResourceCanBeAddedToWorkspaceByRef()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $rootRi = $this->createTree($this->userRoot[0]->getId());
        $this->client->request('GET', "/resource/workspace/add/{$rootRi->{'key'}}/ref/{$this->pwr[0]->getId()}");
        $this->client->request('GET', "/resource/children/{$this->pwr[0]->getId()}");
        $rootDir = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(count($rootDir), 1);
        $this->client->request('GET', "/resource/children/{$rootDir[0]->{'key'}}");
        $file = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(count($file), 2);
        $this->assertEquals(count($this->getUploadedFiles()), 2);
    }

    public function testResourceCanBeAddedToWorkspaceByCopy()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $rootRi = $this->createTree($this->userRoot[0]->getId());
        $this->client->request('GET', "/resource/workspace/add/{$rootRi->{'key'}}/copy/{$this->pwr[0]->getId()}");
        $this->client->request('GET', "/resource/children/{$this->pwr[0]->getId()}");
        $rootDir = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(count($rootDir), 1);
        $this->client->request('GET', "/resource/children/{$rootDir[0]->{'key'}}");
        $file = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(count($file), 1);
        $this->assertEquals(count($this->getUploadedFiles()), 3);
    }

    public function testResourceProportiesCanBeEdited()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request(
            'POST',
            "/resource/update/properties/{$this->pwr[0]->getId()}",
            array('resource_options_form' => array('name' => "EDITED", 'shareType' => 1))
        );

        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals("EDITED", $jsonResponse[0]->{'title'});
        $this->assertEquals(1, $jsonResponse[0]->{'shareType'});
    }

    private function uploadFile($parentId, $name, $shareType = 1)
    {
        $file = new UploadedFile(tempnam(sys_get_temp_dir(), 'FormTest'), $name, 'text/plain', null, null, true);
        $this->client->request(
            'POST',
            "/resource/create/file/{$parentId}",
            array('file_form' => array('shareType' => $shareType)),
            array('file_form' => array('name' => $file))
        );

        $obj = json_decode($this->client->getResponse()->getContent());

        return $obj[0];
    }

    public function createDirectory($parentId, $name, $shareType = 1)
    {
        $this->client->request(
            'POST',
            "/resource/create/directory/{$parentId}",
            array('directory_form' => array('name' => $name, 'shareType' => $shareType))
        );
        $obj = json_decode($this->client->getResponse()->getContent());

        return $obj[0];
    }

    //DIR
        //private child
        //public child
    private function createTree($parentId)
    {
        $rootDir = $this->createDirectory($parentId, 'rootDir');
        $this->uploadFile($rootDir->{'key'}, 'firstfile');
        $this->uploadFile($rootDir->{'key'}, 'secondfile', 0);

        return $rootDir;
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
}