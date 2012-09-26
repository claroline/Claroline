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
        $this->pwr = $this->resourceInstanceRepository->getRootForWorkspace($this->getFixtureReference('user/user')->getPersonalWorkspace());
        $this->userRoot = $this->resourceInstanceRepository->getRootForWorkspace($this->getFixtureReference('workspace/ws_a'));
    }

    public function tearDown()
    {
        parent::tearDown();

        //$this->cleanDirectory($this->upDir);
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
            "/resource/create/directory/{$this->pwr->getId()}",
            array('directory_form' => array('name' => null, 'shareType' => 1))
        );

        $form = $crawler->filter('#directory_form');
        $this->assertEquals(count($form), 1);
    }

    public function testPropertiesFormCanBeDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr->getId(), 'testDir');
        $crawler = $this->client->request('GET', "/resource/form/properties/{$dir->resource_id}");
        $form = $crawler->filter('#resource_options_form');
        $this->assertEquals(count($form), 1);
    }

    public function testPropertiesFormErrorsAreDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr->getId(), 'testDir');
        $crawler = $this->client->request(
            'POST',
            "/resource/update/properties/{$dir->id}",
            array('resource_options_form' => array('name' => '', 'shareType' => 1))
        );

        $form = $crawler->filter('#resource_options_form');
        $this->assertEquals(count($form), 1);
    }

    public function testMoveResource()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr->getId(), 'testDir');
        $res = $this->createDirectory($dir->id, 'childDir');
        $this->client->request(
            'GET',
            "/resource/move/{$res->id}/{$this->pwr->getId()}"
        );
        $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonResponse));
    }

    public function testMultiMove()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->userRoot->getId());
        $theLoneFile = $this->uploadFile($this->userRoot->getId(), 'theLoneFile.txt');
        $theContainer = $this->createDirectory($this->userRoot->getId(), 'container');
        $this->client->request(
            'GET', "/resource/multimove/{$theContainer->id}?0={$theBigTree[0]->id}&1={$theLoneFile->id}"
        );
        $this->client->request('GET', "/resource/children/{$theContainer->id}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonResponse));
    }


    public function testResourceCanBeAddedToWorkspaceByRef()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $rootRi = $this->createTree($this->userRoot->getId());
        $this->client->request('GET', "/resource/workspace/add/{$rootRi[0]->id}/{$this->pwr->getId()}");

        $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $rootDir = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(count($rootDir), 1);
        $keys = array_keys($rootDir);
        $this->client->request('GET', "/resource/children/{$rootDir[$keys[0]]->id}");
        $file = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(count($file), 2);
        $this->assertEquals(count($this->getUploadedFiles()), 2);
        $this->client->request('GET', "/resource/workspace/add/{$this->userRoot->getId()}/{$this->userRoot->getId()}");
        $this->client->request('GET', "/resource/children/{$this->userRoot->getId()}");
        $file = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(count($file), 2);
    }

    public function testMultiAdd()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->userRoot->getId());
        $theLoneFile = $this->uploadFile($this->userRoot->getId(), 'theLoneFile.txt');
        $this->client->request(
            'GET', "/resource/workspace/multi/add/{$this->userRoot->getId()}?0={$theBigTree[0]->id}&1={$theLoneFile->id}"
        );
        $this->client->request('GET', "/resource/children/{$this->userRoot->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(4, count($jsonResponse));
    }

    public function testResourcePropertiesCanBeEdited()
    {
        $this->markTestSkipped('Irrelevant since the name was moved from abstractResource to ResourceInstance');
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request(
            'POST',
            "/resource/update/properties/{$this->pwr->getId()}",
            array('resource_options_form' => array('name' => "EDITED", 'shareType' => 1))
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals("EDITED", $jsonResponse[0]->title);
        $this->assertEquals(1, $jsonResponse[0]->shareType);
    }

    public function testDirectoryDownload()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        //with an empty dir
        $this->client->request('GET', "/resource/export/{$this->userRoot->getId()}");
        $headers = $this->client->getResponse()->headers;
        $name = strtolower(str_replace(' ', '_', $this->userRoot->getName() . '.zip'));
        $this->assertTrue($headers->contains('Content-Disposition', "attachment; filename={$name}"));
        $this->createBigTree($this->userRoot->getId());
        //with a full dir
        $this->client->request('GET', "/resource/export/{$this->userRoot->getId()}");
        $headers = $this->client->getResponse()->headers;
        $this->assertTrue($headers->contains('Content-Disposition', "attachment; filename={$name}"));
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
        $this->assertEquals($jsonResponse[0]->workspace_id, $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId());
    }

    public function testResourceTypesAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', '/resource/types');
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(5, count($jsonResponse));
    }

    public function testResourceListAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->createTree($this->userRoot->getId());
        $fileId = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('type' => 'file'))
            ->getId();
        $this->client->request('GET', "/resource/list/{$fileId}/{$this->userRoot->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonResponse));
    }

    public function testMenusAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', '/resource/menus');
        $jsonResponse = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(5, count($jsonResponse));
    }

    public function testGetEveryInstancesIdsFromMultiExportArray()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->userRoot->getId());
        $toExport = $this->client->getContainer()->get('claroline.resource.exporter')->expandResourceInstanceIds((array) $this->userRoot->getId());
        $this->assertEquals(4, count($toExport));
        $theLoneFile = $this->uploadFile($this->userRoot->getId(), 'theLoneFile.txt');
        $toExport = $this->client->getContainer()->get('claroline.resource.exporter')->expandResourceInstanceIds((array) $theLoneFile->id);
        $this->assertEquals(1, count($toExport));
        $complexExportList = array();
        $complexExportList[] = $theBigTree[0]->id;
        $complexExportList[] = $theLoneFile->id;
        $toExport = $this->client->getContainer()->get('claroline.resource.exporter')->expandResourceInstanceIds($complexExportList);
        $this->assertEquals(5, count($toExport));
    }

    public function testMultiExportClassic()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        //with an empty dir
        $this->client->request('GET', "/resource/multiexport?0={$this->userRoot->getId()}");
        $headers = $this->client->getResponse()->headers;
        $this->assertTrue($headers->contains('Content-Disposition', 'attachment; filename=archive'));
        //with a full dir
        $theBigTree = $this->createBigTree($this->userRoot->getId());
        $theLoneFile = $this->uploadFile($this->userRoot->getId(), 'theLoneFile.txt');
        $this->client->request('GET', "/resource/multiexport?0={$theBigTree[0]->id}&1={$theLoneFile->id}");
        $headers = $this->client->getResponse()->headers;
        $this->assertTrue($headers->contains('Content-Disposition', 'attachment; filename=archive'));
        $filename = $this->client->getContainer()->getParameter('claroline.files.directory').DIRECTORY_SEPARATOR."testMultiExportClassic.zip";
        file_put_contents($filename, $this->client->getResponse()->getContent());
        // Check the archive content
        $zip = new \ZipArchive();
        $zip->open($filename);
        $neededFiles = array(
                "wsA - Workspace_A/rootDir/",
                "wsA - Workspace_A/theLoneFile.txt",
                "wsA - Workspace_A/rootDir/secondfile",
                "wsA - Workspace_A/rootDir/firstfile",
                "wsA - Workspace_A/rootDir/childDir/thirdFile");
        $foundFiles = array();
        for( $i = 0; $i < $zip->numFiles; $i++ ){
            $stat = $zip->statIndex( $i );
            array_push($foundFiles, $stat['name']);
        }
        $this->assertEquals(0, count(array_diff($neededFiles, $foundFiles)));
    }

    public function testMultiExportThrowsAnExceptionWithoutParameters()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/resource/multiexport");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("You must select some resources to export.")')));
    }

    public function testCustomActionThrowExceptionOnUknownAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "resource/custom/directory/thisactiondoesntexists/{$this->pwr->getResource()->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("didn\'t bring back any response")')));
    }

    public function testFilters()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->createBigTree($this->userRoot->getId());
        $this->logUser($this->getFixtureReference('user/admin'));
        $creationTimeAdminTreeOne = new \DateTime();
        $adminpwr = $this->resourceInstanceRepository->getRootForWorkspace($this->getFixtureReference('user/admin')->getPersonalWorkspace());
        $this->createBigTree($adminpwr->getId());
        sleep(2); // Pause to allow us to filter on creation date
        $creationTimeAdminTreeTwo = new \DateTime();
        $wsEroot = $this->resourceInstanceRepository->getRootForWorkspace($this->getFixtureReference('workspace/ws_e'));
        $this->createBigTree($wsEroot->getId());
        $now = new \DateTime();
        //filter by types (1)
        $crawler = $this->client->request('GET', '/resource/filter?types0=file');
        $this->assertEquals(6, count(json_decode($this->client->getResponse()->getContent(), true)));

        //filter by types (2)
        $crawler = $this->client->request('GET', '/resource/filter?types0=file&types1=text');
        $this->assertEquals(6, count(json_decode($this->client->getResponse()->getContent(), true)));

        //filter by root (2)
        $crawler = $this->client->request('GET', "/resource/filter?roots0={$adminpwr->getPath()}&roots1={$wsEroot->getPath()}");
        $this->assertEquals(12, count(json_decode($this->client->getResponse()->getContent(), true)));

        //filter by root (1)
        $crawler = $this->client->request('GET', "/resource/filter?roots0={$adminpwr->getPath()}");
        $this->assertEquals(6, count(json_decode($this->client->getResponse()->getContent(), true)));

        //filter by datecreation
        $crawler = $this->client->request('GET', "/resource/filter?dateFrom={$creationTimeAdminTreeOne->format('Y-m-d H:i:s')}");
        $this->assertEquals(10, count(json_decode($this->client->getResponse()->getContent(), true)));

        $crawler = $this->client->request('GET', "/resource/filter?dateTo={$now->format('Y-m-d H:i:s')}");
        $this->assertEquals(13, count(json_decode($this->client->getResponse()->getContent(), true)));

        $crawler = $this->client->request('GET', "/resource/filter?dateFrom={$creationTimeAdminTreeTwo->format('Y-m-d H:i:s')}&dateTo={$now->format('Y-m-d H:i:s')}");
        $this->assertEquals(5, count(json_decode($this->client->getResponse()->getContent(), true)));

        //filter by name
        $crawler = $this->client->request('GET', "/resource/filter?name=firstFile");
        $this->assertEquals(2, count(json_decode($this->client->getResponse()->getContent())));

        //filter by mime
        $crawler = $this->client->request('GET', "/resource/filter?mimeTypes=text");
        $this->assertEquals(6, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testParents()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $firstDir = $this->createDirectory($this->userRoot->getId(), 'firstDir');
        $file = $this->uploadFile($firstDir->id, 'file');
        $this->client->request('GET', "/resource/parents/{$file->id}");
        $this->assertEquals(3, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testEveryUserInstances()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->createBigTree($this->pwr->getId());
        $this->client->request('GET', '/resource/user/instances/all');
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(3, count($jsonResponse));
    }

    public function testFlatPagination()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->createBigTree($this->pwr->getId());
        $this->client->request('POST', "/resource/instance/flat/1");
        $this->assertEquals(3, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testMultiDelete()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->pwr->getId());
        $theLoneFile = $this->uploadFile($this->pwr->getId(), 'theLoneFile.txt');
        $crawler = $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonResponse));
        $this->client->request(
            'GET',
            "/resource/multidelete?0={$theBigTree[0]->id}&1={$theLoneFile->id}"
        );
        $crawler = $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(0, count($jsonResponse));
    }

    public function testDeleteRootThrowsAnException()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/resource/delete/{$this->userRoot->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("Root directory cannot be removed")')));
    }

    public function testDeleteUserRemovesHisPersonnalDataTree()
    {
        $this->markTestSkipped("Can't make it work.");
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->userRoot->getId());
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', "admin/user/delete/{$this->getFixtureReference('user/user')->getId()}");
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $userRoot = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($this->userRoot->getId());
        $this->assertEquals($userRoot, null);
        $tbg = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($theBigTree[0]->getId());
        $this->assertEquals($tbg, null);
    }

    public function testResourceFilterIsRendered()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', '/resource/filter/renders');
        $this->assertEquals(1, count($crawler->filter('.active-filters')));
    }

    /*
    public function testCountInstances()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->createBigTree($this->pwr->getId());
        $this->client->request('GET', '/resource/count/instances');
        var_dump( $this->client->getResponse()->getContent());
        $this->assertEquals('3', $this->client->getResponse()->getContent());
    }
*/
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
        $arrCreated[] = $this->uploadFile($rootDir->id, 'firstfile');
        $arrCreated[] = $this->uploadFile($rootDir->id, 'secondfile', 0);

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
        $arrCreated[] = $this->uploadFile($rootDir->id, 'firstfile');
        $arrCreated[] = $this->uploadFile($rootDir->id, 'secondfile', 0);
        $arrCreated[] = $childDir = $this->createDirectory($rootDir->id, 'childDir');
        $arrCreated[] = $this->uploadFile($childDir->id, 'thirdFile');

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
