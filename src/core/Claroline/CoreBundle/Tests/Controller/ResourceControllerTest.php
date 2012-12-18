<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class ResourceControllerTest extends FunctionalTestCase
{
    private $resourceRepository;
    private $upDir;
    private $pwr;

    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('user'));
        $this->client->followRedirects();
        $ds = DIRECTORY_SEPARATOR;
        $this->originalPath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
        $this->copyPath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}copy.txt";
        $this->upDir = $this->client->getContainer()->getParameter('claroline.files.directory');
        $this->thumbsDir = $this->client->getContainer()->getParameter('claroline.thumbnails.directory');
        $this->resourceRepository = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $this->pwr = $this->resourceRepository->getRootForWorkspace($this->getFixtureReference('user/user')->getPersonalWorkspace());
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDirectory($this->upDir);
        $this->cleanDirectory($this->thumbsDir);
    }

    public function testRenameFormCanBeDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr->getId(), 'testDir');
        $crawler = $this->client->request('GET', "/resource/rename/form/{$dir->id}");
        $form = $crawler->filter('#resource_name_form');
        $this->assertEquals(count($form), 1);
    }

    public function testRenameFormErrorsAreDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr->getId(), 'testDir');
        $crawler = $this->client->request(
            'POST', "/resource/rename/{$dir->id}",
            array('resource_name_form' => array('name' => ''))
        );
        $form = $crawler->filter('#resource_name_form');
        $this->assertEquals(count($form), 1);
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
            'POST', "/resource/create/directory/{$this->pwr->getId()}", array('directory_form' => array('name' => null, 'shareType' => 1))
        );

        $form = $crawler->filter('#directory_form');
        $this->assertEquals(count($form), 1);
    }

    public function testPropertiesFormCanBeDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr->getId(), 'testDir');
        $crawler = $this->client->request('GET', "/resource/properties/form/{$dir->id}");
        $form = $crawler->filter('#resource_properties_form');
        $this->assertEquals(count($form), 1);
    }

    public function testRename()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr->getId(), 'testDir');
        $this->client->request(
            'POST', "/resource/properties/edit/{$dir->id}",
            array('resource_properties_form' => array('name' => 'new_name'))
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('new_name', $jsonResponse->name);
    }

    public function testChangeIcon()
    {
        $ds = DIRECTORY_SEPARATOR;
        $png = __DIR__."{$ds}..{$ds}Stub{$ds}files{$ds}icon.png";
        copy($png, __DIR__."{$ds}..{$ds}Stub{$ds}files{$ds}iconcopy.png");

        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr->getId(), 'testDir');
        $file = new UploadedFile(__DIR__."{$ds}..{$ds}Stub{$ds}files{$ds}iconcopy.png", 'image.png', 'image/png', null, null, true);
        $this->client->request(
            'POST', "/resource/properties/edit/{$dir->id}",
            array('resource_properties_form' => array('name' => $dir->name)), array('resource_properties_form' => array('userIcon' => $file))
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $images = $this->getUploadedFiles($this->thumbsDir);
        $this->assertEquals(2, count($images));
        $name = str_replace("thumbnails{$ds}", "", $jsonResponse->icon);
        $this->assertContains($name, $images);
    }

    public function testMove()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->pwr->getId());
        $theLoneFile = $this->uploadFile($this->pwr->getId(), 'theLoneFile.txt');
        $theContainer = $this->createDirectory($this->pwr->getId(), 'container');
        $this->client->request(
            'GET', "/resource/move/{$theContainer->id}?ids[]={$theBigTree[0]->id}&ids[]={$theLoneFile->id}"
        );

        $this->client->request('GET', "/resource/children/{$theContainer->id}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonResponse));
    }

    public function testCopy()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->pwr->getId());
        $theLoneFile = $this->uploadFile($this->pwr->getId(), 'theLoneFile.txt');
        $this->client->request(
            'GET', "/resource/copy/{$this->pwr->getId()}?ids[]={$theBigTree[0]->id}&ids[]={$theLoneFile->id}"
        );
        $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(4, count($jsonResponse));
    }

    public function testRootsAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/resource/roots");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($jsonResponse));
    }

    public function testRootAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/resource/root/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($jsonResponse));
        $this->assertEquals($jsonResponse[0]->workspace_id, $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId());
    }

    public function testResourceListAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->createTree($this->pwr->getId());
        $fileId = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('name' => 'file'))
            ->getId();
        $this->client->request('GET', "/resource/list/{$fileId}/{$this->pwr->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonResponse));
    }

    public function testGetEveryInstancesIdsFromExportArray()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->pwr->getId());
        $toExport = $this->client->getContainer()->get('claroline.resource.exporter')->expandResourceIds((array) $this->pwr->getId());
        $this->assertEquals(4, count($toExport));
        $theLoneFile = $this->uploadFile($this->pwr->getId(), 'theLoneFile.txt');
        $toExport = $this->client->getContainer()->get('claroline.resource.exporter')->expandResourceIds((array) $theLoneFile->id);
        $this->assertEquals(1, count($toExport));
        $complexExportList = array();
        $complexExportList[] = $theBigTree[0]->id;
        $complexExportList[] = $theLoneFile->id;
        $toExport = $this->client->getContainer()->get('claroline.resource.exporter')->expandResourceIds($complexExportList);
        $this->assertEquals(5, count($toExport));
    }

    public function testExport()
    {
        $this->markTestSkipped("streamedResponse broke this one");
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/resource/export?0={$this->pwr->getId()}");
        $headers = $this->client->getResponse()->headers;
        $this->assertTrue($headers->contains('Content-Disposition', 'attachment; filename=archive'));
        //with a full dir
        $theBigTree = $this->createBigTree($this->pwr->getId());
        $theLoneFile = $this->uploadFile($this->pwr->getId(), 'theLoneFile.txt');
        $this->client->request('GET', "/resource/multiexport?0={$theBigTree[0]->id}&1={$theLoneFile->id}");
        $headers = $this->client->getResponse()->headers;
        $this->assertTrue($headers->contains('Content-Disposition', 'attachment; filename=archive'));
        $filename = $this->client->getContainer()->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . "testMultiExportClassic.zip";
        ob_start(null);
        $this->client->getResponse()->send();
        $content = ob_get_contents();
        ob_clean();
        file_put_contents($filename, $content);
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
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            array_push($foundFiles, $stat['name']);
        }
        $this->assertEquals(0, count(array_diff($neededFiles, $foundFiles)));
    }

    public function testMultiExportThrowsAnExceptionWithoutParameters()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/resource/export");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("You must select some resources to export.")')));
    }

    public function testCustomActionThrowExceptionOnUknownAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "resource/custom/directory/thisactiondoesntexists/{$this->pwr->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("return any Response")')));
    }

    public function testFilters()
    {
        $this->markTestSkipped();
        $this->logUser($this->getFixtureReference('user/user'));
        $this->createBigTree($this->pwr->getId());
        $this->logUser($this->getFixtureReference('user/admin'));
        $creationTimeAdminTreeOne = new \DateTime();
        $adminpwr = $this->resourceRepository->getRootForWorkspace($this->getFixtureReference('user/admin')->getPersonalWorkspace());
        $this->createBigTree($adminpwr->getId());
//        sleep(2); // Pause to allow us to filter on creation date
//        $creationTimeAdminTreeTwo = new \DateTime();
//        $wsEroot = $this->resourceRepository->getRootForWorkspace($this->getFixtureReference('workspace/ws_e'));
//        $this->createBigTree($wsEroot->getId());
        $now = new \DateTime();
        //filter by types (1)
        $crawler = $this->client->request('GET', '/resource/filter?types[]=file');
        $this->assertEquals(3, count(json_decode($this->client->getResponse()->getContent(), true)));

        //filter by types (2)
        $crawler = $this->client->request('GET', '/resource/filter?types[]=file&types[]=text');
        $this->assertEquals(3, count(json_decode($this->client->getResponse()->getContent(), true)));

        //filter by root (1)
        $crawler = $this->client->request('GET', "/resource/filter?roots[]={$adminpwr->getPath()}");
        $this->assertEquals(5, count(json_decode($this->client->getResponse()->getContent(), true)));

        //filter by datecreation
        $crawler = $this->client->request('GET', "/resource/filter?dateFrom={$creationTimeAdminTreeOne->format('Y-m-d H:i:s')}");
        $this->assertEquals(5, count(json_decode($this->client->getResponse()->getContent(), true)));

        $crawler = $this->client->request('GET', "/resource/filter?dateTo={$now->format('Y-m-d H:i:s')}");
        $this->assertEquals(6, count(json_decode($this->client->getResponse()->getContent(), true)));
//
//        $crawler = $this->client->request('GET', "/resource/filter?dateFrom={$creationTimeAdminTreeTwo->format('Y-m-d H:i:s')}&dateTo={$now->format('Y-m-d H:i:s')}");
//        $this->assertEquals(5, count(json_decode($this->client->getResponse()->getContent(), true)));

        //filter by name
        $crawler = $this->client->request('GET', "/resource/filter?name=firstFile");
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));

        //filter by mime
        /* This filter is not active for now (see ResourceController::filterAction's todo)
        $crawler = $this->client->request('GET', "/resource/filter?mimeTypes[]=text");
        $this->assertEquals(6, count(json_decode($this->client->getResponse()->getContent())));
        */
    }

    public function testParents()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $firstDir = $this->createDirectory($this->pwr->getId(), 'firstDir');
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

    public function testDelete()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->pwr->getId());
        $theLoneFile = $this->uploadFile($this->pwr->getId(), 'theLoneFile.txt');
        $crawler = $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonResponse));
        $this->client->request(
            'GET', "/resource/delete?ids[]={$theBigTree[0]->id}&ids[]={$theLoneFile->id}"
        );
        $crawler = $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(0, count($jsonResponse));
    }

    public function testDeleteRootThrowsAnException()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/resource/delete?ids[]={$this->pwr->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("Root directory cannot be removed")')));
    }

    public function testDeleteUserRemovesHisPersonnalDataTree()
    {
        $this->markTestSkipped("Can't make it work.");
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->pwr->getId());
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', "admin/user/delete/{$this->getFixtureReference('user/user')->getId()}");
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $userRoot = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($this->pwr->getId());
        $this->assertEquals($userRoot, null);
        $tbg = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($theBigTree[0]->getId());
        $this->assertEquals($tbg, null);
    }

    public function testCustomActionLogsEvent()
    {
        $this->markTestSkipped('not custom action defined yet');
        $this->logUser($this->getFixtureReference('user/user'));
        $file = $this->uploadFile($this->pwr->getId(), 'txt.txt');
        $preEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        $this->client->request('GET', "/resource/custom/file/open/{$file->id}");
        $postEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        $this->assertEquals(1, count($postEvents)-count($preEvents));
    }

    public function testOpenActionLogsEvent()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $file = $this->uploadFile($this->pwr->getId(), 'txt.txt');
        $preEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        $this->client->request('GET', "/resource/open/file/{$file->id}");
        $postEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        $this->assertEquals(1, count($postEvents)-count($preEvents));
    }

    public function testCreateActionLogsEvent()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $preEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        $this->uploadFile($this->pwr->getId(), 'txt.txt');
        $postEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        $this->assertEquals(1, count($postEvents) - count($preEvents));
    }

    public function testMultiDeleteActionLogsEvent()
    {
        $this->markTestSkipped("Doesn't work during the test (onLogResource method not fired during the delete). Works otherwise.");
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->pwr->getId());
        $theLoneFile = $this->uploadFile($this->pwr->getId(), 'theLoneFile.txt');
        $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonResponse));
        $preEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        $this->client->request(
            'GET', "/resource/delete?0={$theBigTree[0]->id}&1={$theLoneFile->id}"
        );
        $postEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        $this->assertEquals(2, count($postEvents) - count($preEvents));
    }

    public function testMultiMoveLogsEvent()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->pwr->getId());
        $theLoneFile = $this->uploadFile($this->pwr->getId(), 'theLoneFile.txt');
        $theContainer = $this->createDirectory($this->pwr->getId(), 'container');
        $preEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        $this->client->request(
            'GET', "/resource/move/{$theContainer->id}?ids[]={$theBigTree[0]->id}&ids[]={$theLoneFile->id}"
        );
        $postEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        $this->assertEquals(2, count($postEvents) - count($preEvents));
    }

    public function testMultiExportLogsEvent()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $theBigTree = $this->createBigTree($this->pwr->getId());
        $theLoneFile = $this->uploadFile($this->pwr->getId(), 'theLoneFile.txt');
        $preEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        ob_start();
        $this->client->request('GET', "/resource/export?ids[]={$theBigTree[0]->id}&ids[]={$theLoneFile->id}");
        ob_clean();
        $postEvents = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Logger\ResourceLogger')->findAll();
        $this->assertEquals(5, count($postEvents) - count($preEvents));
    }

    public function testCreateShortcutAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $file = $this->uploadFile($this->pwr->getId(), 'file');
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$file->id}");
        $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonResponse));
    }

    public function testOpenFileShortcut()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $file = $this->uploadFile($this->pwr->getId(), 'file');
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$file->id}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->client->request('GET', "/resource/open/file/{$file->id}");
        $openFile = $this->client->getResponse()->getContent();
        $this->client->request('GET', "/resource/open/file/{$jsonResponse[0]->id}");
        $openShortcut = $this->client->getResponse()->getContent();
        $this->assertEquals($openFile, $openShortcut);

    }

    public function testChildrenShortcut()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $roots = $this->createTree($this->pwr->getId());
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$roots[0]->id}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->client->request('GET', "/resource/children/{$jsonResponse[0]->id}");
        $openShortcut = $this->client->getResponse()->getContent();
        $this->client->request('GET', "/resource/children/{$roots[0]->id}");
        $openDirectory = $this->client->getResponse()->getContent();
        $this->assertEquals($openDirectory, $openShortcut);
    }

    public function testDeleteShortcut()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $file = $this->uploadFile($this->pwr->getId(), 'file');
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$file->id}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->client->request('GET', "/resource/delete?ids[]={$jsonResponse[0]->id}");
        $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($jsonResponse));
    }

    public function testDeleteShortcutTarget()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $file = $this->uploadFile($this->pwr->getId(), 'file');
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$file->id}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->client->request('GET', "/resource/delete?ids[]={$file->id}");
        $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(0, count($jsonResponse));
    }

    public function testEditShortcutIcon()
    {
        $ds = DIRECTORY_SEPARATOR;
        $png = __DIR__."{$ds}..{$ds}Stub{$ds}files{$ds}icon.png";
        copy($png, __DIR__."{$ds}..{$ds}Stub{$ds}files{$ds}iconcopy.png");

        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr->getId(), 'testDir');
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$dir->id}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());

        $file = new UploadedFile(__DIR__."{$ds}..{$ds}Stub{$ds}files{$ds}iconcopy.png", 'image.png', 'image/png', null, null, true);
        $this->client->request(
            'POST', "/resource/properties/edit/{$jsonResponse[0]->id}",
            array('resource_properties_form' => array('name' => $dir->name)), array('resource_properties_form' => array('userIcon' => $file))
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $images = $this->getUploadedFiles($this->thumbsDir);
        $this->assertEquals(2, count($images));
        $name = str_replace("thumbnails{$ds}", "", $jsonResponse->icon);
        $this->assertContains($name, $images);

        //is it the "shortcut" icon ?
        $icon = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
            ->findOneBy(array('relativeUrl' => $jsonResponse->icon));

        $this->assertTrue($icon->isShortcut());
    }

    public function testDisplayRightsForm()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $file = $this->uploadFile($this->pwr->getId(), 'file');
        $crawler = $this->client->request('GET', "/resource/{$file->id}/rights/form");
        $this->assertEquals(3, count($crawler->filter('.row-rights')));
    }

    public function testSubmitRightsForm()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $file = $this->uploadFile($this->pwr->getId(), 'file');
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $file = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($file->id);
        $resourceRights = $this->client->getContainer()->get('claroline.resource.rights')->getRights($file);
        $this->assertEquals(3, count($resourceRights));

        //changes keep the 1st $resourceRight and change the others

        $this->client->request(
            'POST',
            "/resource/{$file->getId()}/rights/edit",
            array(
                 "canSee-{$resourceRights[0]->getId()}" => true,
                 "canSee-{$resourceRights[1]->getId()}" => true,
                 "canDelete-{$resourceRights[1]->getId()}" => true,
             )
        );

        $newRights = $em->getRepository('ClarolineCoreBundle:Workspace\ResourceRights')->getAllForResource($file);
        $resourceRights = $this->client->getContainer()->get('claroline.resource.rights')->getRights($file);
        $this->assertEquals(3, count($resourceRights));
        $this->assertEquals(5, count($newRights));

        $this->client->request(
            'POST',
            "/resource/{$file->getId()}/rights/edit",
            array(
                "canSee-{$resourceRights[0]->getId()}" => true,
                "canDelete-{$resourceRights[1]->getId()}" => true,
            )
        );

        $newRights = $em->getRepository('ClarolineCoreBundle:Workspace\ResourceRights')->getAllForResource($file);
        $this->assertEquals(5, count($newRights));
    }

    public function testDisplayCreationRightForm()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr->getId(), 'dir');
        $crawler = $this->client->request('GET', "/resource/{$dir->id}/role/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getCollaboratorRole()->getId()}/right/creation/form");
        $this->assertEquals(1, count($crawler->filter('#form-resource-creation-rights')));
    }

    public function testSubmitRightsCreationForm()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr->getId(), 'dir');
        $resourceTypes = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findBy(array('isVisible' => true));

        //Creating new ResourceRight from the default one
        $this->client->request(
            'POST',
            "/resource/{$dir->id}/role/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getCollaboratorRole()->getId()}/right/creation/edit",
            array(
                "create-{$resourceTypes[0]->getId()}" => true,
                "create-{$resourceTypes[1]->getId()}" => true,
            )
        );

        //checks if the creation right is set to true now
        $configs = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Workspace\ResourceRights')
            ->findBy(array('role' => $this->getFixtureReference('user/user')->getPersonalWorkspace()->getCollaboratorRole()));

        $this->assertEquals(2, count($configs));

        $config = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Workspace\ResourceRights')
            ->findOneBy(array('resource' => $dir->id));

        $this->assertTrue($config->canCreate());
        $permCreate = $config->getResourceTypes();
        $this->assertEquals(2, count($permCreate));

        //updating the new right
        $this->client->request(
            'POST',
            "/resource/{$dir->id}/role/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getCollaboratorRole()->getId()}/right/creation/edit",
            array(
                "create-{$resourceTypes[1]->getId()}" => true,
                "create-{$resourceTypes[2]->getId()}" => true,
                "create-{$resourceTypes[3]->getId()}" => true,
            )
        );

        $configs = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Workspace\ResourceRights')
            ->findBy(array('role' => $this->getFixtureReference('user/user')->getPersonalWorkspace()->getCollaboratorRole()));

        $this->assertEquals(2, count($configs));

        $config = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Workspace\ResourceRights')
            ->findOneBy(array('resource' => $dir->id));

        $this->assertTrue($config->canCreate());
        $permCreate = $config->getResourceTypes();
        $this->assertEquals(3, count($permCreate));

        //removing perm also remove the canCreate right
        $this->client->request(
            'POST',
            "/resource/{$dir->id}/role/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getCollaboratorRole()->getId()}/right/creation/edit",
            array()
        );

       $config = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Workspace\ResourceRights')
            ->findOneBy(array('resource' => $dir->id));

       $this->assertFalse($config->canCreate());
    }

    private function uploadFile($parentId, $name)
    {
        $file = new UploadedFile(tempnam(sys_get_temp_dir(), 'FormTest'), $name, 'text/plain', null, null, true);
        $this->client->request(
            'POST', "/resource/create/file/{$parentId}", array('file_form' => array()), array('file_form' => array('file' => $file, 'name' => 'tmp'))
        );

        $obj = json_decode($this->client->getResponse()->getContent());
        return $obj[0];
    }

    private function createDirectory($parentId, $name)
    {
        $this->client->request(
            'POST', "/resource/create/directory/{$parentId}", array('directory_form' => array('name' => $name))
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

    private function getUploadedFiles($dir)
    {
        $iterator = new \DirectoryIterator($dir);
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
