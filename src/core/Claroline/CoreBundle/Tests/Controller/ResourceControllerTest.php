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

    public function testDirectoryCreationFormCanBeDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', 'resource/form/directory');
        $form = $crawler->filter('#directory_form');
        $this->assertEquals(count($form), 1);
    }

    public function testDirectoryFormErrorsAreDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request(
            'POST',
            "/resource/create/directory/{$this->pwr[0]->getId()}",
            array('directory_form' => array('name' => null, 'shareType' => 1))
        );

        $form = $crawler->filter('#directory_form');
        $this->assertEquals(count($form), 1);
    }

    public function testPropertiesFormCanBeDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr[0]->getId(), 'testDir');
        $crawler = $this->client->request('GET', "/resource/form/properties/{$dir->resourceId}");
        $form = $crawler->filter('#resource_options_form');
        $this->assertEquals(count($form), 1);
    }

    public function testPropertiesFormErrorsAreDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr[0]->getId(), 'testDir');
        $crawler = $this->client->request(
            'POST',
            "/resource/update/properties/{$dir->instanceId}",
            array('resource_options_form' => array('name' => '', 'shareType' => 1))
        );

        $form = $crawler->filter('#resource_options_form');
        $this->assertEquals(count($form), 1);
    }

    public function testMoveResource()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr[0]->getId(), 'testDir');
        $res = $this->createDirectory($dir->instanceId, 'childDir');
        $this->client->request(
            'GET',
            "/resource/move/{$res->instanceId}/{$this->pwr[0]->getId()}"
        );
        $this->client->request('GET', "/resource/children/{$this->pwr[0]->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonResponse));
    }

    public function testResourceCanBeAddedToWorkspaceByRef()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $rootRi = $this->createTree($this->userRoot[0]->getId());
        $this->client->request('GET', "/resource/workspace/add/{$rootRi[0]->key}/{$this->pwr[0]->getId()}");
        $this->client->request('GET', "/resource/children/{$this->pwr[0]->getId()}");
        $rootDir = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(count($rootDir), 1);
        $this->client->request('GET', "/resource/children/{$rootDir[0]->key}");
        $file = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(count($file), 2);
        $this->assertEquals(count($this->getUploadedFiles()), 2);
    }

    public function testResourceProportiesCanBeEdited()
    {
        $this->markTestSkipped('irrelevant since the name was moved from abstractResource to ResourceInstance');
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request(
            'POST',
            "/resource/update/properties/{$this->pwr[0]->getId()}",
            array('resource_options_form' => array('name' => "EDITED", 'shareType' => 1))
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals("EDITED", $jsonResponse[0]->title);
        $this->assertEquals(1, $jsonResponse[0]->shareType);
    }

    public function testDirectoryDownload()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->createBigTree($this->userRoot[0]->getId());
        $this->client->request('GET', "/resource/export/{$this->userRoot[0]->getId()}");
        $headers = $this->client->getResponse()->headers;
        $name = strtolower(str_replace(' ', '_', $this->userRoot[0]->getName() . '.zip'));
        $this->assertTrue($headers->contains('Content-Disposition', "attachment; filename={$name}"));


        //the code below doesn't work yet.
        //the archive content should be tested

//        $content = $this->client->getResponse()->getContent();
//        $tmpname = tempnam(sys_get_temp_dir(), 'dlarch');
//        file_put_contents($tmpname, $content);
//        $tmparch = new \ZipArchive;
//        $res = $tmparch->open($tmpname);
//        var_dump($tmparch->getFromName('my workspace/rootDir/firstFile'));

    }

    public function testRootsAction()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', "/resource/roots");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(3, count($jsonResponse));
    }

    public function testRootAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/resource/root/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($jsonResponse));
        $this->assertEquals($jsonResponse[0]->workspaceId, $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId());
    }

    public function testResourceTypesAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', '/resource/types');
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        //HTMLElement, Forum, file, text
        //directory is not included
        //only listable included
        $this->assertEquals(4, count($jsonResponse));
    }

    public function testResourceListAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->createTree($this->userRoot[0]->getId());
        $fileId = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('type' => 'file'))
            ->getId();
        $this->client->request('GET', "/resource/list/{$fileId}/{$this->userRoot[0]->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonResponse));
    }

    public function testMenusAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', '/resource/menus');
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(10, count(get_object_vars($jsonResponse)));
    }

    public function testGetEveryInstancesIdsFromTheClassicMultiExportArray()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->userRoot[0]->getId());
        $toExport = $this->client->getContainer()->get('claroline.resource.manager')->getClassicExportList((array) $this->userRoot[0]->getId());
        $this->assertEquals(3, count($toExport));
        $theLoneFile = $this->uploadFile($this->userRoot[0]->getId(), 'theLoneFile.txt');
        $toExport = $this->client->getContainer()->get('claroline.resource.manager')->getClassicExportList((array) $theLoneFile->key);
        $this->assertEquals(1, count($toExport));
        $complexExportList = array();
        $complexExportList[] = $theBigTree[0]->key;
        $complexExportList[] = $theLoneFile->key;
        $toExport = $this->client->getContainer()->get('claroline.resource.manager')->getClassicExportList($complexExportList);
        $this->assertEquals(4, count($toExport));
    }

    public function testMultiExportClassic()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->userRoot[0]->getId());
        $theLoneFile = $this->uploadFile($this->userRoot[0]->getId(), 'theLoneFile.txt');
        $this->client->request(
            'GET',
            "/resource/multiexport/classic?0={$theBigTree[0]->key}&1={$theLoneFile->key}"
        );
        $headers = $this->client->getResponse()->headers;
        $this->assertTrue($headers->contains('Content-Disposition', 'attachment; filename=archive'));
        //the archive content should be tested
    }

    //this test should be improved but there is no other "exportable" resource atm.
    public function testMultiExportLinker()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->createBigTree($this->userRoot[0]->getId());
        $pseudoId = 'file_'.$this->userRoot[0]->getId();
        $this->client->request(
            'GET',
            "/resource/multiexport/linker?0={$pseudoId}"
        );
        $headers = $this->client->getResponse()->headers;
        $this->assertTrue($headers->contains('Content-Disposition', 'attachment; filename=archive'));
        //the archive content should be tested
    }

    public function testGetEveryInstancesIdsFromTheLinkerMultiExportArray()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->userRoot[0]->getId());
        $this->createForum($theBigTree[0]->key, 'lonelyForum');
        $pseudoId = 'file_'.$this->userRoot[0]->getId();
        $toExport = $this->client->getContainer()->get('claroline.resource.manager')->getLinkerExportList((array) $pseudoId);
        $this->assertEquals(3, count($toExport));
    }

    public function testCustomActionThrowExceptionOnUknownAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "resource/custom/directory/thisactiondoesntexists/{$this->pwr[0]->getResource()->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("didn\'t bring back any response")')));
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

    public function createForum($parentId, $name, $shareType = 1)
    {
        $this->client->request(
            'POST',
            "/resource/create/forum/{$parentId}",
            array('forum_form' => array('name' => $name, 'shareType' => $shareType))
        );

        $obj = json_decode($this->client->getResponse()->getContent());

        return $obj[0];
    }

    //DIR
        //private child
        //public child
    private function createTree($parentId)
    {
        $arrCreated = array();
        $arrCreated[] = $rootDir = $this->createDirectory($parentId, 'rootDir');
        $arrCreated[] = $this->uploadFile($rootDir->key, 'firstfile');
        $arrCreated[] = $this->uploadFile($rootDir->key, 'secondfile', 0);

        return $arrCreated;
    }

    //DIR
        //private child
        //public child
        //private dir
            //private child
    private function createBigTree($parentId)
    {
        $arrCreated = array();
        $arrCreated[] = $rootDir = $this->createDirectory($parentId, 'rootDir');
        $arrCreated[] = $this->uploadFile($rootDir->key, 'firstfile');
        $arrCreated[] = $this->uploadFile($rootDir->key, 'secondfile', 0);
        $arrCreated[] = $childDir = $this->createDirectory($rootDir->key, 'childDir');
        $arrCreated[] = $this->uploadFile($childDir->key, 'thirdFile');

        return $arrCreated;
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