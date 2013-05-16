<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\Resource\Directory;

/**
 * @todo Test the exception if a directory id parameter doesn't match any directory.
 * @todo Test filters when not in the Desktop (workspaceId != 0).
 */
class ResourceControllerTest extends FunctionalTestCase
{
    private $resourceRepository;
    private $logRepository;
    private $pwr;

    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->client->followRedirects();
        $this->resourceRepository = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $this->logRepository = $this->em->getRepository('ClarolineCoreBundle:Logger\Log');
        $this->pwr = $this->getDirectory('user');
    }

    public function testDirectoryCreationFormCanBeDisplayed()
    {
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', 'resource/form/directory');
        $form = $crawler->filter('#directory_form');
        $this->assertEquals(count($form), 1);
    }

    public function testDirectoryFormErrorsAreDisplayed()
    {
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request(
            'POST',
            "/resource/create/directory/{$this->pwr->getId()}",
            array('directory_form' => array('name' => null, 'shareType' => 1))
        );

        $form = $crawler->filter('#directory_form');
        $this->assertEquals(count($form), 1);
    }

    public function testMove()
    {
        $now = new \DateTime();

        $this->loadFileData('user', 'user', array('file.txt'));
        $this->loadDirectoryData('user', array('user/container'));
        $this->createBigTree('user');
        $this->logUser($this->getUser('user'));
        $treeRoot = $this->getDirectory('treeRoot');
        $loneFile = $this->getFile('file.txt');
        $container = $this->getDirectory('container');
        $this->client->request(
            'GET',
            "/resource/move/{$container->getId()}?ids[]={$treeRoot->getId()}&ids[]={$loneFile->getId()}"
        );
        $this->client->request('GET', "/resource/directory/{$this->getDirectory('container')->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(2, count($dir->resources));

        $logs = $this->logRepository->findActionAfterDate(
            'resource_move',
            $now,
            $this->getUser('user')->getId(),
            $loneFile->getId()
        );
        $this->assertEquals(1, count($logs));
    }

    public function testCopy()
    {
        $now = new \DateTime();

        $this->loadFileData('user', 'user', array('file.txt'));
        $this->createBigTree('user');
        $this->logUser($this->getUser('user'));
        $treeRoot = $this->getDirectory('treeRoot');
        $loneFile = $this->getFile('file.txt');
        $this->client->request(
            'GET',
            "/resource/copy/{$this->pwr->getId()}?ids[]={$treeRoot->getId()}&ids[]={$loneFile->getId()}"
        );

        $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(4, count($dir->resources));

        $logs = $this->logRepository->findActionAfterDate(
            'resource_copy',
            $now,
            $this->getUser('user')->getId()
        );
        $this->assertEquals(6, count($logs));
    }

    public function testGetEveryInstancesIdsFromExportArray()
    {
        $this->loadFileData('user', 'user', array('file.txt'));
        $this->createBigTree('user');
        $this->logUser($this->getUser('user'));
        $toExport = $this->client
            ->getContainer()
            ->get('claroline.resource.exporter')
            ->expandResourceIds((array) $this->getDirectory('treeRoot')->getId());
        $this->assertEquals(4, count($toExport));
        $toExport = $this->client
            ->getContainer()
            ->get('claroline.resource.exporter')
            ->expandResourceIds((array) $this->getFile('file.txt')->getId());
        $this->assertEquals(1, count($toExport));
        $complexExportList = array();
        $complexExportList[] = $this->pwr->getId();
        $complexExportList[] = $this->getFile('file.txt')->getId();
        $toExport = $this->client
            ->getContainer()
            ->get('claroline.resource.exporter')
            ->expandResourceIds($complexExportList);
        $this->assertEquals(6, count($toExport));
    }

    public function testExport()
    {
        $now = new \DateTime();

        $this->logUser($this->getUser('user'));
        ob_start();
        $this->client->request('GET', "/resource/export?ids[]={$this->pwr->getId()}");
        ob_end_clean();
        $headers = $this->client->getResponse()->headers;
        $this->assertTrue($headers->contains('Content-Disposition', 'attachment; filename=archive'));

        $logs = $this->logRepository->findActionAfterDate(
            'resource_export',
            $now,
            $this->getUser('user')->getId(),
            $this->pwr->getId()
        );
        $this->assertEquals(1, count($logs));
    }

    public function testMultiExportThrowsAnExceptionWithoutParameters()
    {
        $now = new \DateTime();

        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', "/resource/export");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(
            1,
            count($crawler->filter('html:contains("You must select some resources to export.")'))
        );

        $logs = $this->logRepository->findActionAfterDate(
            'resource_export',
            $now,
            $this->getUser('user')->getId()
        );
        $this->assertEquals(0, count($logs));
    }

    public function testCustomActionThrowExceptionOnUknownAction()
    {
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request(
            'GET',
            "resource/custom/directory/thisactiondoesntexist/{$this->pwr->getId()}"
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("return any Response")')));
    }

    public function testNameFilter()
    {
        $this->createBigTree('user');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/filter/0?name=file1");
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($result->resources));
    }

    public function testTypeFilter()
    {
        $this->createBigTree('user');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', '/resource/filter/0?types[]=file');
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(3, count($result->resources));
    }

    public function testDateFilter()
    {
        sleep(1);
        $timeOne = new \DateTime();
        sleep(1);
        $this->createBigTree('user');
        sleep(1);
        $timeTwo = new \DateTime();
        sleep(1);
        $this->loadFileData('user', 'dir2', array('file4.pdf'));
        $this->logUser($this->getUser('user'));

        $this->client->request(
            'GET',
            "/resource/filter/0?dateFrom={$timeOne->format('Y-m-d H:i:s')}"
        );
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(6, count($result->resources));

        $this->client->request(
            'GET',
            "/resource/filter/0?dateFrom={$timeTwo->format('Y-m-d H:i:s')}"
        );
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($result->resources));

        $this->client->request(
            'GET',
            "/resource/filter/0?dateFrom={$timeOne->format('Y-m-d H:i:s')}&dateTo={$timeTwo->format('Y-m-d H:i:s')}"
        );
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(5, count($result->resources));
    }

    public function testMimeFilter()
    {
        $this->markTestSkipped('This filter is not active for now (see ResourceController::filterAction\'s todo)');
    }

    public function testDelete()
    {
        $now = new \DateTime();

        $this->createBigTree('user');
        $this->loadFileData('user', 'user', array('file.txt'));
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(2, count($dir->resources));
        $this->client->request(
            'GET',
            "/resource/delete?ids[]={$this->getDirectory('treeRoot')->getId()}&"
            . "ids[]={$this->getFile('file.txt')->getId()}"
        );
        $crawler = $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(0, count($dir->resources));

        $logs = $this->logRepository->findActionAfterDate(
            'resource_delete',
            $now,
            $this->getUser('user')->getId()
        );
        $this->assertEquals(6, count($logs));
    }

    public function testDeleteRootThrowsAnException()
    {
        $now = new \DateTime();

        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', "/resource/delete?ids[]={$this->pwr->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("Root directory cannot be removed")')));

        $logs = $this->logRepository->findActionAfterDate(
            'resource_delete',
            $now,
            $this->getUser('user')->getId()
        );
        $this->assertEquals(0, count($logs));
    }

    public function testCustomActionLogsEvent()
    {
        $this->markTestSkipped('no custom action defined yet');
        $this->loadFileData('user', 'user', array('file.txt'));
        $file = $this->getFile('file.txt');
        $this->logUser($this->getUser('user'));
        $preEvents = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findAll();
        $this->client->request('GET', "/resource/custom/file/open/{$file->getId()}");
        $postEvents = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findAll();
        $this->assertEquals(1, count($postEvents) - count($preEvents));
    }

    public function testOpenActionLogsEvent()
    {
        $now = new \DateTime();

        $this->loadFileData('user', 'user', array('file.txt'));
        $file = $this->getFile('file.txt');
        $this->logUser($this->getUser('user'));
        $preEvents = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findAll();
        $this->client->request('GET', "/resource/open/file/{$file->getId()}");
        $postEvents = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findAll();
        $this->assertEquals(1, count($postEvents) - count($preEvents));

        $logs = $this->logRepository->findActionAfterDate(
            'resource_read',
            $now,
            $this->getUser('user')->getId(),
            $file->getId()
        );
        $this->assertEquals(1, count($logs));
    }

    public function testCreateActionLogsEventWithResourceManager()
    {
        $now = new \DateTime();

        $user = $this->getUser('user');
        $this->logUser($user);
        $logRepo = $this->em->getRepository('ClarolineCoreBundle:Logger\Log');
        $preEvents = $logRepo->findAll();
        $this->client->request(
            'POST',
            "/resource/create/directory/{$this->pwr->getId()}",
            array('directory_form' => array()),
            array('directory_form' => array('name' => 'name'))
        );
        $postEvents = $logRepo->findAll();
        $this->assertEquals(1, count($postEvents) - count($preEvents));

        $logs = $this->logRepository->findActionAfterDate(
            'resource_create',
            $now,
            $this->getUser('user')->getId()
        );
        $this->assertEquals(1, count($logs));
    }

    public function testMultiDeleteActionLogsEvent()
    {
        $now = new \DateTime();

        $this->createBigTree('user');
        $this->loadFileData('user', 'user', array('file.txt'));
        $treeRoot = $this->getDirectory('treeRoot');
        $loneFile = $this->getFile('file.txt');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(2, count($dir->resources));
        $preEvents = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findAll();
        $this->client->request(
            'GET', "/resource/delete?ids[]={$treeRoot->getId()}&ids[]={$loneFile->getId()}"
        );

        $postEvents = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findAll();
        $this->assertEquals(6, count($postEvents) - count($preEvents));

        $logs = $this->logRepository->findActionAfterDate(
            'resource_delete',
            $now,
            $this->getUser('user')->getId()
        );
        $this->assertEquals(6, count($logs));
    }

    public function testMultiMoveLogsEvent()
    {
        $now = new \DateTime();

        $this->createBigTree('user');
        $this->loadFileData('user', 'user', array('file.txt'));
        $this->loadDirectoryData('user', array('user/container'));
        $container = $this->getDirectory('container');
        $treeRoot = $this->getDirectory('treeRoot');
        $loneFile = $this->getFile('file.txt');
        $this->logUser($this->getUser('user'));
        $preEvents = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findAll();
        $this->client->request(
            'GET',
            "/resource/move/{$container->getId()}?ids[]={$treeRoot->getId()}&ids[]={$loneFile->getId()}"
        );
        $postEvents = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findAll();
        $this->assertEquals(2, count($postEvents) - count($preEvents));

        $logs = $this->logRepository->findActionAfterDate(
            'resource_move',
            $now,
            $this->getUser('user')->getId()
        );
        $this->assertEquals(2, count($logs));
    }

    public function testMultiExportLogsEvent()
    {
        $now = new \DateTime();

        $this->createBigTree('user');
        $this->loadFileData('user', 'user', array('file.txt'));
        $treeRoot = $this->getDirectory('treeRoot');
        $loneFile = $this->getFile('file.txt');
        $this->logUser($this->getUser('user'));
        $preEvents = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findAll();
        ob_start();
        $this->client->request(
            'GET',
            "/resource/export?ids[]={$treeRoot->getId()}&ids[]={$loneFile->getId()}"
        );
        ob_clean();
        $postEvents = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findAll();
        $this->assertEquals(5, count($postEvents) - count($preEvents));

        $logs = $this->logRepository->findActionAfterDate(
            'resource_export',
            $now,
            $this->getUser('user')->getId()
        );
        $this->assertEquals(5, count($logs));
    }

    public function testCreateShortcutAction()
    {
        $this->loadFileData('user', 'user', array('file.txt'));
        $file = $this->getFile('file.txt');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$file->getId()}");
        $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(2, count($dir->resources));
    }

    public function testOpenFileShortcut()
    {
        $this->loadFileData('user', 'user', array('file.txt'));
        $file = $this->getFile('file.txt');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$file->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->client->request('GET', "/resource/open/file/{$file->getId()}");
        $openFile = $this->client->getResponse()->getContent();
        $this->client->request('GET', "/resource/open/file/{$jsonResponse[0]->id}");
        $openShortcut = $this->client->getResponse()->getContent();
        $this->assertEquals($openFile, $openShortcut);
    }

    public function testChildrenShortcut()
    {
        $this->createBigTree('user');
        $rootDir = $this->getDirectory('treeRoot');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$rootDir->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->client->request('GET', "/resource/directory/{$jsonResponse[0]->id}");
        $openShortcut = $this->client->getResponse()->getContent();
        $this->client->request('GET', "/resource/directory/{$rootDir->getId()}");
        $openDirectory = $this->client->getResponse()->getContent();
        $this->assertEquals($openDirectory, $openShortcut);
    }

    public function testDeleteShortcut()
    {
        $this->loadFileData('user', 'user', array('file.txt'));
        $file = $this->getFile('file.txt');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$file->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->client->request('GET', "/resource/delete?ids[]={$jsonResponse[0]->id}");
        $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(1, count($dir->resources));
    }

    public function testDeleteShortcutTarget()
    {
        $this->loadFileData('user', 'user', array('file.txt'));
        $file = $this->getFile('file.txt');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$file->getId()}");
        $this->client->request('GET', "/resource/delete?ids[]={$file->getId()}");
        $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(0, count($dir->resources));
    }

    public function testOpenDirectoryAction()
    {
        $this->loadDirectoryData('user', array('user/Foo/Bar'));
        $this->loadFileData('user', 'Bar', array('Baz'));
        $this->loadFileData('user', 'Bar', array('Bat'));
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/directory/{$this->getDirectory('Bar')->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('path', $jsonResponse);
        $this->assertObjectHasAttribute('creatableTypes', $jsonResponse);
        $this->assertObjectHasAttribute('resources', $jsonResponse);
        $this->assertEquals(3, count($jsonResponse->path));
        $this->assertEquals(5, count((array) $jsonResponse->creatableTypes));
        $this->assertEquals(2, count((array) $jsonResponse->resources));
    }

    public function testOpenDirectoryReturnsTheRootDirectoriesIfDirectoryIdIsZero()
    {
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/directory/0");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('path', $jsonResponse);
        $this->assertObjectHasAttribute('creatableTypes', $jsonResponse);
        $this->assertObjectHasAttribute('resources', $jsonResponse);
        $this->assertEquals(0, count($jsonResponse->path));
        $this->assertEquals(0, count((array) $jsonResponse->creatableTypes));
        $this->assertEquals(1, count((array) $jsonResponse->resources));
    }

    public function testOpenDirectoryThrowsAnExceptionIfDirectoryDoesntExist()
    {
        $now = new \DateTime();

        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/directory/123456");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());

        $logs = $this->logRepository->findActionAfterDate(
            'resource_read',
            $now,
            $this->getUser('user')->getId(),
            123456
        );
        $this->assertEquals(0, count($logs));
    }

    public function testOpenDirectoryThrowsAnExceptionIfResourceIsNotADirectory()
    {
        $now = new \DateTime();

        $this->loadFileData('user', 'user', array('Bar'));
        $file = $this->getFile('Bar');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/directory/{$file->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());

        $logs = $this->logRepository->findActionAfterDate(
            'resource_read',
            $now,
            $this->getUser('user')->getId(),
            $file->getId()
        );
        $this->assertEquals(0, count($logs));
    }

    private function createBigTree($userReferenceName)
    {
        $this->loadDirectoryData($userReferenceName, array($userReferenceName.'/treeRoot/dir2'));
        $this->loadFileData($userReferenceName, 'treeRoot', array('file1.pdf'));
        $this->loadFileData($userReferenceName, 'treeRoot', array('file2.pdf'));
        $this->loadFileData($userReferenceName, 'dir2', array('file3.pdf'));
    }
}
