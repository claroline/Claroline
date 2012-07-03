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
    /** @var string */
    private $filePath;

    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadFixture(new LoadResourceTypeData());
        $this->client->followRedirects();
        $ds = DIRECTORY_SEPARATOR;
        $this->originalPath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
        $this->copyPath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}copy.txt";
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

    public function testOwnerCanAddRemoveResourcePermission()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->registerToWorkspaceA();
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $root = $this->addRootFile($this->getFixtureReference('workspace/ws_a')->getId());
        $this->client->request('GET',"/resource/permission/add/{$root->getId()}/{$this->getFixtureReference('user/user')->getId()}/128");
        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertTrue($this->client->getContainer()->get('security.context')->isGranted(('OWNER'), $root));
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request('GET',"/resource/permission/remove/{$root->getId()}/{$this->getFixtureReference('user/user')->getId()}/128");
        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertFalse($this->client->getContainer()->get('security.context')->isGranted(('OWNER'), $root));
    }

    public function testManagerCanAddDefaultResourcePermissionsForWorkspace()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->addRootFile($this->getFixtureReference('workspace/ws_a')->getId());
        $roleId = $this->getFixtureReference('workspace/ws_a')->getCollaboratorRole()->getId();
        $this->client->request('GET',"/workspace/add/role/permission/{$roleId}/128");
        $newMask = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:WorkspaceRole')->find($roleId)->getResMask();
        $this->assertEquals($newMask, 129);
    }

    public function testManagerCanRemoveDefaultResourcePermissionsForWorkspace()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->addRootFile($this->getFixtureReference('workspace/ws_a')->getId());
        $roleId = $this->getFixtureReference('workspace/ws_a')->getCollaboratorRole()->getId();
        $this->client->request('GET',"/workspace/add/role/permission/{$roleId}/128");
        $newMask = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:WorkspaceRole')->find($roleId)->getResMask();
        $this->assertEquals($newMask, 129);
        $this->client->request('GET',"/workspace/remove/role/permission/{$roleId}/128");
        $newMask = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:WorkspaceRole')->find($roleId)->getResMask();
        $this->assertEquals($newMask, 1);
    }

    public function testAddRemoveResourcePermissionIsProtected()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $root = $this->addRootFile($this->getFixtureReference('workspace/ws_a')->getId());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->registerToWorkspaceA();
        $this->client->request('GET',"/resource/permission/add/{$root->getId()}/{$this->getFixtureReference('user/user')->getId()}/128");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET',"/resource/permission/remove/{$root->getId()}/{$this->getFixtureReference('user/user')->getId()}/128");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testNormalWorkspaceDefaultActionProtection()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $root = $this->addRootFile($this->getFixtureReference('workspace/ws_a')->getId());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/resource/click/{$root->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->registerToWorkspaceA();
        $this->client->request('GET', "/resource/click/{$root->getId()}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testCreatorCanAccessPersonnalWorkspaceResourceDefaultAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $ri = $this->addRootFile($this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
        $this->client->request('GET', "/resource/click/{$ri->getId()}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testResourceOpenActionIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $ri = $this->addRootFile($this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
        $this->logUser($this->getFixtureReference('user/user_2'));
        $this->client->request('GET', "/resource/open/{$ri->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testCreatorCanAccessResourceOpenAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $ri = $this->addRootFile($this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
        $this->client->request('GET', "/resource/open/{$ri->getId()}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testPersonnalWorkspaceResourceDeleteActionIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $ri = $this->addRootFile($this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
        $this->logUser($this->getFixtureReference('user/user_2'));
        $this->client->request('GET', "/resource/workspace/remove/{$ri->getId()}/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * CASE    user permissions    role permissions
     * ============ unregistered ====================
     * unregistered: no right.
     * ============= registered =====================
     * CASE 1:    user: NONE    => collaborator: VIEW  => VIEW :: default registration
     * CASE 2:    user: OWNER   => collaborator: VIEW  => from VIEW to OWNER
     * CASE 3:    user: NONE    => collaborator: from VIEW to OWNER and from OWNER to VIEW
     */

    public function testIsGrantedInstanceReturnsTheCorrectValueWhenUpdated()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $root = $this->addRootFile($this->getFixtureReference('workspace/ws_a')->getId());
        // unregistered
        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertFalse($this->client->getContainer()->get('security.context')->isGranted('VIEW', $root));
        //registration
        $this->registerToWorkspaceA();
        // |1| user: none & collaborator: VIEW => VIEW
        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertTrue($this->client->getContainer()->get('security.context')->isGranted('VIEW', $root));
        $this->assertFalse($this->client->getContainer()->get('security.context')->isGranted('OWNER', $root));
        // |2| user: owner & collaborator: VIEW => OWNER
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request('GET',"/resource/permission/add/{$root->getId()}/{$this->getFixtureReference('user/user')->getId()}/128");
        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertTrue($this->client->getContainer()->get('security.context')->isGranted('OWNER', $root));
        // |3| user: none & collaborator: OWNER. Resource added before change
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $root = $this->addRootFile($this->getFixtureReference('workspace/ws_a')->getId());
        $roleId = $this->getFixtureReference('workspace/ws_a')->getCollaboratorRole()->getId();
        $this->client->request('GET',"/workspace/add/role/permission/{$roleId}/128");
        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertTrue($this->client->getContainer()->get('security.context')->isGranted('OWNER', $root));
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request('GET',"/workspace/remove/role/permission/{$roleId}/128");
        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertFalse($this->client->getContainer()->get('security.context')->isGranted('OWNER', $root));
        //user: none & collaborator: OWNER. Resource added after change
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $roleId = $this->getFixtureReference('workspace/ws_a')->getCollaboratorRole()->getId();
        $this->client->request('GET',"/workspace/add/role/permission/{$roleId}/128");
        $root = $this->addRootFile($this->getFixtureReference('workspace/ws_a')->getId());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertTrue($this->client->getContainer()->get('security.context')->isGranted('OWNER', $root));
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $roleId = $this->getFixtureReference('workspace/ws_a')->getCollaboratorRole()->getId();
        $this->client->request('GET',"/workspace/add/role/permission/{$roleId}/128");
        $this->client->request('GET',"/workspace/remove/role/permission/{$roleId}/128");
        $root = $this->addRootFile($this->getFixtureReference('workspace/ws_a')->getId());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertFalse($this->client->getContainer()->get('security.context')->isGranted('OWNER', $root));
    }

    public function testRemoveUserRight()
    {
//      $this->markTestSkipped('will be implemented later, the user role should be replaced');
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        //$this->registerToWorkspaceA();
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $root = $this->addRootFile($this->getFixtureReference('workspace/ws_a')->getId());
        $roleId = $this->getFixtureReference('workspace/ws_a')->getCollaboratorRole()->getId();
        $this->client->request('GET',"/workspace/add/role/permission/{$roleId}/128");
        $this->client->request('GET',"/resource/permission/add/{$root->getId()}/{$this->getFixtureReference('user/user')->getId()}/128");
        var_dump($this->client->getResponse()->getContent());
        $this->client->request('GET',"/resource/permission/remove/{$root->getId()}/{$this->getFixtureReference('user/user')->getId()}/1");
        var_dump($this->client->getResponse()->getContent());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertFalse($this->client->getContainer()->get('security.context')->isGranted('VIEW', $root));
    }


    public function testCreatorCanAccessDeleteAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $ri = $this->addRootFile($this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
        $this->client->request('GET', "/resource/workspace/remove/{$ri->getId()}/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testResourceCanBeAddedToWorkspaceByRef()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->initWorkspaceATestsByRef($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
    }

    public function testRegisterUserHasAccessToWorkspaceResourcesByRef()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->initWorkspaceATestsByRef($this->getFixtureReference('user/user'));
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
        $rootRi = $this->initWorkspaceATestsByRef($this->getFixtureReference('user/user'));
        $this->logUser($this->getFixtureReference('user/user_2'));
        $this->registerToWorkspaceA();
        $this->unregisterFromWorkspaceA();
        $this->client->request('GET', "/resource/click/{$rootRi->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET', "/resource/click/{$rootRi->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testResourceCanBeAddedToWorkspaceByCopy()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->initWorkspaceATestsByCopy($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/workspace/show/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
    }

    public function testRegisterUserHasAccessToWorkspaceResourcesByCopy()
    {
        $this->loadFixture(new LoadWorkspaceData());
        $this->logUser($this->getFixtureReference('user/user'));
        $this->initWorkspaceATestsByCopy($this->getFixtureReference('user/user'));
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
        $this->initWorkspaceATestsByCopy($this->getFixtureReference('user/user'));
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
        $this->initWorkspaceATestsByCopy($this->getFixtureReference('user/user'));
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

    public function testResourceOptionsCanBeEdited()
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
}