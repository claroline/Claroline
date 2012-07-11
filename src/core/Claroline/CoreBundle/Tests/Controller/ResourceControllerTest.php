<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile as SfFile;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadWorkspaceData;
use Claroline\CoreBundle\DataFixtures\LoadMimeTypeData;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;

class ResourceControllerTest extends FunctionalTestCase
{
    private $upDir;

    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadFixture(new LoadResourceTypeData());
        $this->client->followRedirects();
        $ds = DIRECTORY_SEPARATOR;
        $this->originalPath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
        $this->copyPath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}copy.txt";
        $this->upDir = $this->client->getContainer()->getParameter('claroline.files.directory');
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->cleanDirectory($this->upDir);
    }

    public function testUserCanCreateFileResource()
    {
        $this->markTestSkipped('crsf token error');
        $this->logUser($this->getFixtureReference('user/user'));
        copy($this->originalPath, $this->copyPath);
        $file = new SfFile($this->copyPath, 'copy.txt');

        $this->client->request(
            'POST',
            "resource/add/file/null/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}",
            array('shareType' => 1, 'name' => $this->copyPath),
            array('name' => $file),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'PHP_AUTH_USER' => $this->getFixtureReference('user/user')->getUsername(),
                'PHP_AUTH_PW' => '123'
                )
        );
    }

    public function testResourceCanBeAddedToWorkspaceByRef()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->initWorkspaceATestsByRef($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
        $link = $crawler->filter('.link_resource_view')->first()->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
        $this->assertEquals(count($this->getUploadedFiles()),3);
    }

    public function testResourceCanBeAddedToWorkspaceByCopy()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->initWorkspaceATestsByCopy($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
        $link = $crawler->filter('.link_resource_view')->first()->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
        $this->assertEquals(count($this->getUploadedFiles()), 4);
    }

    public function testResourceProportiesCanBeEdited()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $rootRi = $this->addRootDirectory($this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId(), 'root_dir');
        $crawler = $this->client->request('GET', "/resource/form/options/{$rootRi->getId()}");
        $form = $crawler->filter('input[type=submit]')->form();
        $form['resource_options_form[name]'] = "EDITED";
        $form['resource_options_form[shareType]'] = 1;
        $crawler = $this->client->submit($form);
        $res = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($rootRi->getId())->getResource();
        $this->assertEquals($this->getFixtureReference('user/user')->getId(), $res->getCreator()->getId());
        $this->assertEquals("EDITED", $res->getName());
        $this->assertEquals(1, $res->getShareType());
        $this->assertNotEquals($res->getCreationDate(), $res->getModificationDate());
    }

    private function initWorkspaceATestsByRef($user)
    {
        $rootRi = $this->createTree($user->getPersonnalWorkspace()->getId());
        $this->registerToWorkspaceA();
        $crawler = $this->client->request('GET', '/workspace/list');
        $id = $crawler->filter(".row_workspace")->first()->attr('data-workspace_id');
        $link = $crawler->filter("#link_show_{$id}")->link();
        $this->client->click($link);
        $this->client->request('GET', "/resource/workspace/add/{$rootRi->getId()}/{$this->getFixtureReference('workspace/ws_a')->getId()}/ref");

        return $rootRi;
    }

    private function initWorkspaceATestsByCopy($user)
    {
        $rootRi = $this->createTree($user->getPersonnalWorkspace()->getId());
        $this->registerToWorkspaceA();
        $crawler = $this->client->request('GET', '/workspace/list');
        $id = $crawler->filter(".row_workspace")->first()->attr('data-workspace_id');
        $link = $crawler->filter("#link_show_{$id}")->link();
        $this->client->click($link);
        $this->client->request('GET', "/resource/workspace/add/{$rootRi->getId()}/{$this->getFixtureReference('workspace/ws_a')->getId()}/copy");

        return $rootRi;
    }

    /**
     * Creates a resource and return the resource instance
     *
     * @param AbstractResource $object
     * @param integer          $workspaceId
     * @param integer          $parentId
     *
     * @return ResourceInstance
     */
    private function addResource($object, $workspaceId, $parentId = null)
    {
        return $ri = $this
            ->client
            ->getContainer()
            ->get('claroline.resource.creator')
            ->create(
                $object,
                $workspaceId,
                $parentId,
                true
                );
    }

    private function addRootFile($wsId)
    {
        copy($this->originalPath, $this->copyPath);
        $file = new SfFile($this->copyPath, 'copy.txt', null, null, null, true);
        $object = new File();
        $object->setName($file);
        $object->setShareType(1);

        return $this->addResource($object, $wsId, null);
    }

    private function createTree($wsId)
    {
        $rootRi = $this->addRootDirectory($wsId, 'rootDir');
        $this->addRootFile($wsId);
        $firstFile = new File();
        $secondFile = new File();
        copy($this->originalPath, $this->copyPath);
        $firstCopy = new SfFile($this->copyPath, 'copy.txt', null, null, null, true);
        $firstFile->setName($firstCopy);
        $firstFile->setShareType(0);
        $this->addResource($firstFile, $wsId, $rootRi->getId());
        $secondFile->setShareType(1);
        copy($this->originalPath, $this->copyPath);
        $secondCopy = new SfFile($this->copyPath, 'copy.txt', null, null, null, true);
        $secondFile->setName($secondCopy);
        $this->addResource($secondFile, $wsId, $rootRi->getId());

        return $rootRi;
    }

    private function addRootDirectory($wsId, $name)
    {
        $rootDir = new Directory();
        $rootDir->setName($name);
        $rootDir->setShareType(1);

        return $this->addResource($rootDir, $wsId);;
    }

    private function registerToWorkspaceA()
    {
        $crawler = $this->client->request('GET', '/workspace/list');
        $link = $crawler->filter("#link_registration_{$this->getFixtureReference('workspace/ws_a')->getId()}")->link();
        $this->client->click($link);
    }

    private function unregisterFromWorkspaceA()
    {
        $crawler = $this->client->request('GET', '/workspace/list');
        $link = $crawler->filter("#link_unregistration_{$this->getFixtureReference('workspace/ws_a')->getId()}")->link();
        $this->client->click($link);
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