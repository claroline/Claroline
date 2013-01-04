<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;

class ResourceVoterTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('user', 'ws_creator'));
        $this->manager = $this->getFixtureReference('user/ws_creator');
        $em = $this->getEntityManager();
        $this->root = $em
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->getRootForWorkspace($this->manager->getPersonalWorkspace());
        $this->rootRights = $em->getRepository('ClarolineCoreBundle:Workspace\ResourceRights')->getRights($this->manager, $this->root);
    }

    public function testOpenResource()
    {
        $em = $this->getEntityManager();

        $this->logUser($this->manager);
        $this->assertTrue($this->getSecurityContext()->isGranted('OPEN', new ResourceCollection(array($this->root))));

        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertFalse($this->getSecurityContext()->isGranted('OPEN', new ResourceCollection(array($this->root))));

        $this->logUser($this->manager);
        $this->rootRights->setCanOpen(false);
        $em->persist($this->rootRights);
        $em->flush();
        $this->assertFalse($this->getSecurityContext()->isGranted('OPEN', new ResourceCollection(array($this->root))));

    }

    public function testEditResource()
    {
        $em = $this->getEntityManager();

        $this->logUser($this->manager);
        $this->assertTrue($this->getSecurityContext()->isGranted('EDIT', new ResourceCollection(array($this->root))));

        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertFalse($this->getSecurityContext()->isGranted('EDIT', new ResourceCollection(array($this->root))));

        $this->logUser($this->manager);
        $this->rootRights->setCanEdit(false);
        $em->persist($this->rootRights);
        $em->flush();
        $this->assertFalse($this->getSecurityContext()->isGranted('EDIT', new ResourceCollection(array($this->root))));
    }

    public function testDeleteResource()
    {
        $em = $this->getEntityManager();

        $this->logUser($this->manager);
        $this->assertTrue($this->getSecurityContext()->isGranted('DELETE', new ResourceCollection(array($this->root))));

        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertFalse($this->getSecurityContext()->isGranted('DELETE', new ResourceCollection(array($this->root))));

        $this->logUser($this->manager);
        $this->rootRights->setCanDelete(false);
        $em->persist($this->rootRights);
        $em->flush();
        $this->assertFalse($this->getSecurityContext()->isGranted('DELETE', new ResourceCollection(array($this->root))));
    }

    public function testExportResource()
    {
        $em = $this->getEntityManager();

        $this->logUser($this->manager);
        $this->assertTrue($this->getSecurityContext()->isGranted('EXPORT', new ResourceCollection(array($this->root))));

        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertFalse($this->getSecurityContext()->isGranted('EXPORT', new ResourceCollection(array($this->root))));

        $this->logUser($this->manager);
        $this->rootRights->setCanExport(false);
        $em->persist($this->rootRights);
        $em->flush();
        $this->assertFalse($this->getSecurityContext()->isGranted('EXPORT', new ResourceCollection(array($this->root))));
    }

    public function testCreateResource()
    {
        $em = $this->getEntityManager();

        $this->logUser($this->manager);
        $this->assertTrue($this->getSecurityContext()->isGranted('CREATE', new ResourceCollection(array($this->root), array('type' => 'directory'))));

        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertFalse($this->getSecurityContext()->isGranted('CREATE', new ResourceCollection(array($this->root), array('type' => 'directory'))));

        $this->logUser($this->manager);
        $fileType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array('name' => 'file'));
        $this->rootRights->addResourceType($fileType);
        $em->persist($this->rootRights);
        $em->flush();
        $this->assertFalse($this->getSecurityContext()->isGranted('CREATE', new ResourceCollection(array($this->root), array('type' => 'directory'))));
    }

    public function testCopyResource()
    {
        $em = $this->getEntityManager();

        $resourceManager = $this->client->getContainer()->get('claroline.resource.manager');
        $directory = new Directory();
        $directory->setName('NEWDIR');
        $directory = $resourceManager->create($directory, $this->root->getId(), 'directory', null, $this->manager);

        $this->logUser($this->manager);
        $this->assertTrue($this->getSecurityContext()->isGranted('COPY', new ResourceCollection(array($directory), array('parent' => $this->root))));

        $this->logUser($this->getFixtureReference('user/user'));
        $this->assertFalse($this->getSecurityContext()->isGranted('COPY', new ResourceCollection(array($directory), array('parent' => $this->root))));
    }

    public function testMoveResource()
    {
        $this->markTestSkipped('Waiting the RoleVoterFix');
    }
}