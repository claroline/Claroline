<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class ResourceVoterTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->manager = $this->getUser('ws_creator');
        $em = $this->getEntityManager();
        $this->roleWsManager = $em->getRepository('ClarolineCoreBundle:Role')
            ->findOneBy(array('name' => 'ROLE_WS_MANAGER_'.$this->manager->getPersonalWorkspace()->getId()));
        $this->root = $this->getDirectory('ws_creator');
        $this->rootRights = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findOneBy(array('resource' => $this->root, 'role' => $this->roleWsManager));
    }

    public function testOpenResource()
    {
        $em = $this->getEntityManager();

        $this->logUser($this->manager);
        $this->assertTrue(
            $this->getSecurityContext()
                ->isGranted('OPEN', new ResourceCollection(array($this->root)))
        );

        $this->logUser($this->getUser('user'));
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('OPEN', new ResourceCollection(array($this->root)))
        );

        $this->logUser($this->manager);
        $this->rootRights->setCanOpen(false);
        $em->persist($this->rootRights);
        $em->flush();
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('OPEN', new ResourceCollection(array($this->root)))
        );

    }

    public function testEditResource()
    {
        $em = $this->getEntityManager();

        $this->logUser($this->manager);
        $this->assertTrue(
            $this->getSecurityContext()
                ->isGranted('EDIT', new ResourceCollection(array($this->root)))
        );

        $this->logUser($this->getUser('user'));
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('EDIT', new ResourceCollection(array($this->root)))
        );

        $this->logUser($this->manager);
        $this->rootRights->setCanEdit(false);
        $em->persist($this->rootRights);
        $em->flush();
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('EDIT', new ResourceCollection(array($this->root)))
        );
    }

    public function testDeleteResource()
    {
        $em = $this->getEntityManager();

        $this->logUser($this->manager);
        $this->assertTrue(
            $this->getSecurityContext()
                ->isGranted('DELETE', new ResourceCollection(array($this->root)))
        );

        $this->logUser($this->getUser('user'));
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('DELETE', new ResourceCollection(array($this->root)))
        );

        $this->logUser($this->manager);
        $this->rootRights->setCanDelete(false);
        $em->persist($this->rootRights);
        $em->flush();
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('DELETE', new ResourceCollection(array($this->root)))
        );
    }

    public function testExportResource()
    {
        $em = $this->getEntityManager();

        $this->logUser($this->manager);
        $this->assertTrue(
            $this->getSecurityContext()
                ->isGranted('EXPORT', new ResourceCollection(array($this->root)))
        );

        $this->logUser($this->getUser('user'));
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('EXPORT', new ResourceCollection(array($this->root)))
        );

        $this->logUser($this->manager);
        $this->rootRights->setCanExport(false);
        $em->persist($this->rootRights);
        $em->flush();
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('EXPORT', new ResourceCollection(array($this->root)))
        );
    }

    public function testCreateResource()
    {
        $em = $this->getEntityManager();

        $this->logUser($this->manager);
        $this->assertTrue(
            $this->getSecurityContext()
                ->isGranted('CREATE', new ResourceCollection(array($this->root), array('type' => 'directory')))
        );

        $this->logUser($this->getUser('user'));
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('CREATE', new ResourceCollection(array($this->root), array('type' => 'directory')))
        );

        $this->logUser($this->manager);
        $directoryType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneBy(array('name' => 'directory'));
        $this->rootRights->removeCreatableResourceType($directoryType);
        $em->persist($this->rootRights);
        $em->flush();
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('CREATE', new ResourceCollection(array($this->root), array('type' => 'directory')))
        );
    }

    public function testCopyResource()
    {
        $em = $this->getEntityManager();
        $this->loadDirectoryData('user', array('ws_creator/directory'));
        $directory = $this->getDirectory('directory');

        $this->logUser($this->manager);
        $this->assertTrue(
            $this->getSecurityContext()
                ->isGranted('COPY', new ResourceCollection(array($directory), array('parent' => $this->root)))
        );

        $this->logUser($this->getUser('user'));
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('COPY', new ResourceCollection(array($directory), array('parent' => $this->root)))
        );

        $this->logUser($this->manager);
        $directoryType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneBy(array('name' => 'directory'));
        $this->rootRights->removeCreatableResourceType($directoryType);
        $directoryRights = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findOneBy(array('resource' => $directory, 'role' => $this->roleWsManager));
        $directoryRights->setCanCopy(false);
        $em->persist($this->rootRights);
        $em->persist($directoryRights);
        $em->flush();
        $collection = new ResourceCollection(array($directory), array('parent' => $this->root));
        $this->assertFalse($this->getSecurityContext()->isGranted('COPY', $collection));
        $this->assertEquals(2, count($collection->getErrors()));
    }

    public function testMoveResource()
    {
        $em = $this->getEntityManager();

        $resourceManager = $this->client->getContainer()->get('claroline.resource.manager');
        $directory = new Directory();
        $directory->setName('NEWDIR');
        $directory = $resourceManager->create($directory, $this->root->getId(), 'directory', $this->manager);

        $this->logUser($this->manager);
        $this->assertTrue(
            $this->getSecurityContext()
                ->isGranted('MOVE', new ResourceCollection(array($directory), array('parent' => $this->root)))
        );

        $this->logUser($this->getUser('user'));
        $this->assertFalse(
            $this->getSecurityContext()
                ->isGranted('MOVE', new ResourceCollection(array($directory), array('parent' => $this->root)))
        );

        $this->logUser($this->manager);
        $directoryType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneBy(array('name' => 'directory'));
        $this->rootRights->removeCreatableResourceType($directoryType);
        $directoryRights = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findOneBy(array('resource' => $directory, 'role' => $this->roleWsManager));
        $directoryRights->setCanCopy(false);
        $directoryRights->setCanDelete(false);
        $em->persist($this->rootRights);
        $em->persist($directoryRights);

        $em->flush();
        $collection = new ResourceCollection(array($directory), array('parent' => $this->root));
        $this->assertFalse($this->getSecurityContext()->isGranted('MOVE', $collection));
        $this->assertEquals(3, count($collection->getErrors()));
    }

    public function testAnAuthenticationExceptionIsThrownIfAnonymousUserHasInsufficientPermissionsOnResource()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AuthenticationException');
        $context = $this->getSecurityContext();
        $context->setToken(new AnonymousToken('some_key', 'anon.', array('ROLE_ANONYMOUS')));
        // this could/should be tested for each resource permission
        $this->getSecurityContext()->isGranted('OPEN', new ResourceCollection(array($this->root)));
    }
}