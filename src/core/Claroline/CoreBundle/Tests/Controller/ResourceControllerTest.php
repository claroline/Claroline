<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadWorkspaceData;
use Claroline\CoreBundle\DataFixtures\LoadMimeTypeData;

class ResourceControllerTest extends FunctionalTestCase
{
    /** @var string */
    private $filePath;

    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadFixture(new LoadResourceTypeData());
        $this->loadFixture(new LoadMimeTypeData());
        $this->client->followRedirects();
        $ds = DIRECTORY_SEPARATOR;
        $this->filePath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
    }

    public function testUserCanCreateFileResource()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->addRootFile($this->filePath);
        $crawler = $this->client->request('GET', '/resource/directory/null');
        $this->assertEquals($crawler->filter('.row_resource')->count(), 1);
    }

    public function testResourceDefaultActionIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $id = $this->addRootFile($this->filePath);
        $this->logUser($this->getFixtureReference('user/user_2'));
        $this->client->request('GET', "/resource/click/{$id}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testCreatorCanAccessResourceDefaultAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $id = $this->addRootFile($this->filePath);
        $this->client->request('GET', "/resource/click/{$id}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testResourceOpenActionIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $id = $this->addRootFile($this->filePath);
        $this->logUser($this->getFixtureReference('user/user_2'));
        $workspace = $this->findResourceWorkspace($id);
        $this->client->request('GET', "/resource/open/{$workspace->getId()}/{$id}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testCreatorCanAccessResourceOpenAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $id = $this->addRootFile($this->filePath);
        $workspace = $this->findResourceWorkspace($id);
        $this->client->request('GET', "/resource/open/{$workspace->getId()}/{$id}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testResourceDeleteActionIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $id = $this->addRootFile($this->filePath);
        $this->logUser($this->getFixtureReference('user/user_2'));
        $this->client->request('GET', "/resource/workspace/remove/{$id}/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testCreatorCanAccessDeleteAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $id = $this->addRootFile($this->filePath);
        $this->client->request('GET', "/resource/workspace/remove/{$id}/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testResourceCanBeAddedToWorkspaceByRef()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->initWorkspacesTestsByRef();
        $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
    }

    public function testRegisterUserHasAccessToWorkspaceResourcesByRef()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->initWorkspacesTestsByRef();
        $this->logUser($this->getFixtureReference('user/user_2'));
        $this->registerToWorkspaceA();
        $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $link = $crawler->filter('.link_resource_view')->first()->link();
        $this->client->click($link);
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
        $link = $crawler->filter('.link_resource_view')->first()->link();
        $this->client->click($link);
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
    }

    public function testUnregisteredUserLostAccessToWorkspaceResourcesByRef()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $rootId = $this->initWorkspacesTestsByRef();
        $this->logUser($this->getFixtureReference('user/user_2'));
        $this->registerToWorkspaceA();
        $this->unregisterFromWorkspaceA();
        $this->client->request('GET', "/resource/click/{$rootId}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testResourceCanBeAddedToWorkspaceByCopy()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->initWorkspacesTestsByCopy();
        $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
    }

    public function testRegisterUserHasAccessToWorkspaceResourcesByCopy()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->initWorkspacesTestsByCopy();
        $this->logUser($this->getFixtureReference('user/user_2'));
        $this->registerToWorkspaceA();
        $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $link = $crawler->filter('.link_resource_view')->first()->link();
        $this->client->click($link);
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
        $link = $crawler->filter('.link_resource_view')->first()->link();
        $this->client->click($link);
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
    }

    public function testUnregisteredUserLostAccessToWorkspaceResourcesByCopy()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->initWorkspacesTestsByCopy();
        $this->logUser($this->getFixtureReference('user/user_2'));
        $this->registerToWorkspaceA();
        $this->unregisterFromWorkspaceA();
        $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $link = $crawler->filter('.link_resource_view')->first()->link();
        $this->client->click($link);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testRegisteredUserHasAccesToWorkspaceResourcesByCopy()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->registerToWorkspaceA();
        $this->logUser($this->getFixtureReference('user/user_2'));
        $this->initWorkspacesTestsByCopy();
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $link = $crawler->filter('.link_resource_view')->first()->link();
        $this->client->click($link);
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
        $link = $crawler->filter('.link_resource_view')->first()->link();
        $this->client->click($link);
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
        $link = $crawler->filter('.link_resource_view')->first()->link();
        $this->client->click($link);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    private function initWorkspacesTestsByRef()
    {
        $rootId = $this->createResourcesTree();
        $this->registerToWorkspaceA();
        $crawler = $this->client->request('GET', '/workspace/list');
        $id = $crawler->filter(".row_workspace")->first()->attr('data-workspace_id');
        $link = $crawler->filter("#link_show_{$id}")->link();
        $this->client->click($link);
        //add root to workspace
        $this->client->request('GET', "/resource/workspace/add/{$rootId}/{$this->getFixtureReference('workspace/ws_a')->getId()}/ref");
        return $rootId;
    }

    private function initWorkspacesTestsByCopy()
    {
        $rootId = $this->createResourcesTree();
        $this->registerToWorkspaceA();
        $crawler = $this->client->request('GET', '/workspace/list');
        $id = $crawler->filter(".row_workspace")->first()->attr('data-workspace_id');
        $link = $crawler->filter("#link_show_{$id}")->link();
        $this->client->click($link);
        //add root to workspace
        $this->client->request('GET', "/resource/workspace/add/{$rootId}/{$this->getFixtureReference('workspace/ws_a')->getId()}/copy");
    }

    private function addRootFile($filePath)
    {
        $crawler = $this->client->request('GET', '/desktop');
        $link = $crawler->filter('#resource_manager_link')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('input[type=submit]')->form();
        $fileTypeId = $this->getFixtureReference('resource_type/file')->getId();
        $crawler = $this->client->submit($form, array('select_resource_form[type]' => $fileTypeId));
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('file_form[name]' => $filePath));
        $id = $crawler->filter(".row_resource")->last()->attr('data-resource_id');

        return $id;
    }

    private function addRootDirectory($name)
    {
        $crawler = $this->client->request('GET', '/resource/directory/null');
        $form = $crawler->filter('input[type=submit]')->form();
        $fileTypeId = $this->getFixtureReference('resource_type/directory')->getId();
        $crawler = $this->client->submit($form, array('select_resource_form[type]' => $fileTypeId));
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('directory_form[name]' => $name));
        $id = $crawler->filter(".row_resource")->last()->attr('data-resource_id');

        return $id;
    }

    private function addFileInCurrentDirectory($filePath, $crawler)
    {
        $form = $crawler->filter('input[type=submit]')->form();
        $fileTypeId = $this->getFixtureReference('resource_type/file')->getId();
        $crawler = $this->client->submit($form, array('select_resource_form[type]' => $fileTypeId));
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('file_form[name]' => $filePath));
        $crawler->filter(".row_resource")->last()->attr('data-resource_id');
    }

    private function createResourcesTree()
    {
        $id = $this->addRootDirectory('ROOT_DIRECTORY_user');
        $crawler = $this->client->request('GET', '/resource/directory/null');
        $link = $crawler->filter("#link_resource_{$id}")->link();
        $crawler = $this->client->click($link);
        $this->addFileInCurrentDirectory($this->filePath, $crawler);

        return $id;
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

    private function findResourceWorkspace($resourceId)
    {
        $resourceInstance = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($resourceId);
        $workspace = $resourceInstance->getWorkspace();

        return $workspace;
    }
}
