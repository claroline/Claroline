<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Role;

class ResourceRightsControllerTest extends FunctionalTestCase
{
    private $logRepository;

    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('john' => 'user'));
        $this->logRepository = $this->em->getRepository('ClarolineCoreBundle:Logger\Log');
    }

    public function testDisplayRightsForm()
    {
        $this->loadFileData('john', 'john', array('test.pdf'));
        $this->logUser($this->getUser('john'));
        $crawler = $this->client->request('GET', "/resource/{$this->getFile('test.pdf')->getId()}/rights/form/role");
        // admin rights shouldn't be displayed
        $this->assertEquals(4, count($crawler->filter('.role-permissions')));
    }

    public function testSubmitRightsForm()
    {
        $now = new \DateTime();

        $this->loadDirectoryData('john', array('john/dir1'));
        $this->loadFileData('john', 'dir1', array('test.pdf'));
        $this->logUser($this->getUser('john'));
        $rightsRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $roleRepo = $this->em->getRepository('ClarolineCoreBundle:Role');
        $userRights = $rightsRepo->findOneBy(
            array(
                'resource' => $this->getDirectory('dir1'),
                'role' => $roleRepo->findWorkspaceRoleForUser(
                    $this->getUser('john'),
                    $this->getWorkspace('john')
                )
            )
        );

        // set open and edit permissions to true recursively for user role
        $this->client->request(
            'POST',
            "/resource/{$this->getDirectory('dir1')->getId()}/rights/edit",
            array(
                'roles' => array(
                    $userRights->getRole()->getId() => array(
                        'open' => 'on',
                        'edit' => 'on'
                    )
                ),
                'isRecursive' => 'on'
            )
        );
        $this->assertRolePermissionsOnResource(
            $userRights->getRole(),
            $this->getDirectory('dir1'),
            array('delete' => false, 'open' => true, 'export' => false, 'copy' => false, 'edit' => true),
            array('delete' => false, 'open' => true, 'export' => false, 'copy' => false, 'edit' => true)
        );

        // set delete, export and copy permissions to true for user role on root resource only
        $this->client->request(
            'POST',
            "/resource/{$this->getDirectory('dir1')->getId()}/rights/edit",
            array(
                'roles' => array(
                    $userRights->getRole()->getId() => array(
                        'delete' => 'on',
                        'export' => 'on',
                        'copy' => 'on'
                    )
                )
            )
        );
        $this->assertRolePermissionsOnResource(
            $userRights->getRole(),
            $this->getDirectory('dir1'),
            array('delete' => true, 'open' => false, 'export' => true, 'copy' => true, 'edit' => false),
            array('delete' => false, 'open' => true, 'export' => false, 'copy' => false, 'edit' => true)
        );

        // check that admin permissions are still set to true
        $this->assertRolePermissionsOnResource(
            $this->getRole('admin'),
            $this->getDirectory('dir1'),
            array('delete' => true, 'open' => true, 'export' => true, 'copy' => true, 'edit' => true),
            array('delete' => true, 'open' => true, 'export' => true, 'copy' => true, 'edit' => true)
        );

        // check that other roles permissions are set to false (as not passed in previous requests)
        foreach ($rightsRepo->findNonAdminRights($this->getDirectory('dir1')) as $rights) {
            if ($rights != $userRights) {
                $this->assertRolePermissionsOnResource(
                    $rights->getRole(),
                    $this->getDirectory('dir1'),
                    array('delete' => false, 'open' => false, 'export' => false, 'copy' => false, 'edit' => false),
                    array('delete' => false, 'open' => false, 'export' => false, 'copy' => false, 'edit' => false)
                );
            }
        }

        $logs = $this->logRepository->findActionAfterDate(
            'ws_role_change_right',
            $now,
            $this->getUser('john')->getId(),
            $this->getDirectory('dir1')->getId()
        );
        $this->assertEquals(3, count($logs));
    }

    public function testDisplayCreationRightForm()
    {
        $this->loadDirectoryData('john', array('john/dir1'));
        $directoryId = $this->getDirectory('dir1')->getId();
        $collaboratorRoleId = $this->em->getRepository('ClarolineCoreBundle:Role')
            ->findCollaboratorRole($this->getWorkspace('john'))
            ->getId();
        $this->logUser($this->getUser('john'));
        $crawler = $this->client->request(
            'GET',
            "/resource/{$directoryId}/role/{$collaboratorRoleId}/right/creation/form"
        );
        $this->assertEquals(1, count($crawler->filter('#form-resource-creation-rights')));
    }

    public function testSubmitRightsCreationForm()
    {
        $now = new \DateTime();

        $this->loadDirectoryData('john', array('john/dir1', 'john/dir1/dir2'));
        $rightsRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $directoryId = $this->getDirectory('dir1')->getId();
        $collaboratorRoleId = $this->em->getRepository('ClarolineCoreBundle:Role')
            ->findCollaboratorRole($this->getWorkspace('john'))
            ->getId();
        $resourceTypes = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findAll();
        $this->logUser($this->getUser('john'));

        $this->client->request(
            'POST',
            "/resource/{$directoryId}/role/{$collaboratorRoleId}/right/creation/edit",
            array(
                'resourceTypes' => array(
                    $resourceTypes[0]->getId() => 'on',
                    $resourceTypes[1]->getId() => 'on'
                ),
                'isRecursive' => 'on'
            )
        );

        $this->em->clear();

        $firstDirCreatableTypes = $rightsRepo->findOneBy(
            array('resource' => $this->getDirectory('dir1'), 'role' => $collaboratorRoleId)
        );
        $secondDirCreatableTypes = $rightsRepo->findOneBy(
            array('resource' => $this->getDirectory('dir2'), 'role' => $collaboratorRoleId)
        );
        $this->assertEquals(
            array($resourceTypes[0], $resourceTypes[1]),
            $firstDirCreatableTypes->getCreatableResourceTypes()->toArray()
        );
        $this->assertEquals(
            array($resourceTypes[0], $resourceTypes[1]),
            $secondDirCreatableTypes->getCreatableResourceTypes()->toArray()
        );

        // allow three other creatable types on root resource only
        $this->client->request(
            'POST',
            "/resource/{$directoryId}/role/{$collaboratorRoleId}/right/creation/edit",
            array(
                'resourceTypes' => array(
                    $resourceTypes[1]->getId() => 'on',
                    $resourceTypes[2]->getId() => 'on',
                    $resourceTypes[3]->getId() => 'on'
                )
            )
        );

        $this->em->refresh($firstDirCreatableTypes);
        $this->em->refresh($secondDirCreatableTypes);

        $this->assertEquals(
            array($resourceTypes[1], $resourceTypes[2], $resourceTypes[3]),
            $firstDirCreatableTypes->getCreatableResourceTypes()->toArray()
        );
        $this->assertEquals(
            array($resourceTypes[0], $resourceTypes[1]), // only the first dir should be changed
            $secondDirCreatableTypes->getCreatableResourceTypes()->toArray()
        );

        $logs = $this->logRepository->findActionAfterDate(
            'ws_role_change_right',
            $now,
            $this->getUser('john')->getId(),
            $directoryId
        );
        $this->assertEquals(2, count($logs));
    }

    public function testAddRightToRoleOutsideWorkspace()
    {
        $this->loadUserData(array('jane' => 'user'));
        $this->loadDirectoryData('john', array('john/dir1', 'john/dir1/dir2'));
        $this->logUser($this->getUser('john'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $directoryId = $this->getDirectory('dir1')->getId();
        $role = $em->getRepository('ClarolineCoreBundle:Role')
            ->findManagerRole($this->getWorkspace('jane'));
        $this->client->request(
            'POST',
            "/resource/{$directoryId}/role/{$role->getId()}/right/create",
            array('resources_rights_form' => array(
                'canOpen' => true,
                'canEdit' => true,
                'canDelete' => true,
                'canCopy' => true,
                'canExport' => true,
            ))
        );

        $resourceRightsRepo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $rights = $resourceRightsRepo->findNonAdminRights($this->getDirectory('dir1'));
        $this->assertEquals(5, count($rights));
        //tesing the recursivity
        $preRecursive = count($resourceRightsRepo->findRecursiveByResource($this->getDirectory('dir1')));

        $this->client->request(
            'POST',
            "/resource/{$this->getDirectory('dir1')->getId()}/rights/edit",
            array(
                'roles' => array(),
                'isRecursive' => 'on'
            )
        );

        $postRecursive = count($resourceRightsRepo->findRecursiveByResource($this->getDirectory('dir1')));
        $this->assertEquals(1, $postRecursive - $preRecursive);
    }

    public function testRecursiveRightCreationCreatesMissingRights()
    {
        $this->loadUserData(array('jane' => 'user'));
        $this->loadDirectoryData('john', array('john/dir1', 'john/dir1/dir2'));
        $this->logUser($this->getUser('john'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $directoryId = $this->getDirectory('dir1')->getId();
        $role = $em->getRepository('ClarolineCoreBundle:Role')
            ->findManagerRole($this->getWorkspace('jane'));
        $resourceRightsRepo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $preRecursive = count($resourceRightsRepo->findRecursiveByResource($this->getDirectory('dir1')));
        $this->client->request(
            'POST',
            "/resource/{$directoryId}/role/{$role->getId()}/right/create",
            array('resources_rights_form' => array(
                'canOpen' => true,
                'canEdit' => true,
                'canDelete' => true,
                'canCopy' => true,
                'canExport' => true,
                'isRecursive' => true
            ))
        );

        $postRecursive = count($resourceRightsRepo->findRecursiveByResource($this->getDirectory('dir1')));
        $this->assertEquals(2, $postRecursive - $preRecursive);
    }

    public function testRecursiveRightCreationOverridesExistingRights()
    {
        $this->loadUserData(array('jane' => 'user'));
        $this->loadDirectoryData('john', array('john/dir1', 'john/dir1/dir2'));
        $this->logUser($this->getUser('john'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $role = $em->getRepository('ClarolineCoreBundle:Role')
            ->findManagerRole($this->getWorkspace('jane'));

        $this->client->request(
            'POST',
            "/resource/{$this->getDirectory('dir2')->getId()}/role/{$role->getId()}/right/create",
            array('resources_rights_form' => array(
                'canOpen' => true,
                'canEdit' => true,
                'canDelete' => true,
                'canCopy' => true,
                'canExport' => true,
            ))
        );

        $this->client->request(
            'POST',
            "/resource/{$this->getDirectory('dir1')->getId()}/role/{$role->getId()}/right/create",
            array('resources_rights_form' => array(
                'isRecursive' => true
            ))
        );

        $this->assertRolePermissionsOnResource(
            $role,
            $this->getDirectory('dir1'),
            array('delete' => false, 'open' => false, 'export' => false, 'copy' => false, 'edit' => false),
            array('delete' => false, 'open' => false, 'export' => false, 'copy' => false, 'edit' => false)
        );
    }

    public function testRecursiveRightEditionOverridedExistingRights()
    {
        $this->loadUserData(array('jane' => 'user'));
        $this->loadDirectoryData('john', array('john/dir1', 'john/dir1/dir2'));
        $this->logUser($this->getUser('john'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $role = $em->getRepository('ClarolineCoreBundle:Role')
            ->findManagerRole($this->getWorkspace('jane'));

        $this->client->request(
            'POST',
            "/resource/{$this->getDirectory('dir1')->getId()}/role/{$role->getId()}/right/create",
            array('resources_rights_form' => array(
                'canOpen' => true,
                'canEdit' => true,
                'canDelete' => true,
                'canCopy' => true,
                'canExport' => true,
                'isRecursive' => true
            ))
        );

        $resourceRightId = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findOneBy(array('role' => $role, 'resource' => $this->getDirectory('dir1')))->getId();

        $this->client->request(
            'POST',
            "/resource/right/{$resourceRightId}/edit",
            array('resources_rights_form' => array(
                'canOpen' => true,
                'canEdit' => true,
                'canCopy' => true,
                'canExport' => true,
                'isRecursive' => true
            ))
        );

        $this->assertRolePermissionsOnResource(
            $role,
            $this->getDirectory('dir1'),
            array('delete' => false, 'open' => true, 'export' => true, 'copy' => true, 'edit' => true),
            array('delete' => false, 'open' => true, 'export' => true, 'copy' => true, 'edit' => true)
        );
    }

    public function testRecursiveRightEditionCreatesMissingRights()
    {
        $this->loadUserData(array('jane' => 'user'));
        $this->loadDirectoryData('john', array('john/dir1', 'john/dir1/dir2'));
        $this->logUser($this->getUser('john'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $role = $em->getRepository('ClarolineCoreBundle:Role')
            ->findManagerRole($this->getWorkspace('jane'));

        $this->client->request(
            'POST',
            "/resource/{$this->getDirectory('dir1')->getId()}/role/{$role->getId()}/right/create",
            array('resources_rights_form' => array(
                'canOpen' => true,
                'canEdit' => true,
                'canDelete' => true,
                'canCopy' => true,
                'canExport' => true,
            ))
        );

        $resourceRightsRepo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $preRecursive = count($resourceRightsRepo->findRecursiveByResource($this->getDirectory('dir1')));
        $resourceRightId = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findOneBy(array('role' => $role, 'resource' => $this->getDirectory('dir1')))->getId();

       var_dump('r2');
       $this->client->request(
            'POST',
            "/resource/right/{$resourceRightId}/edit",
            array('resources_rights_form' => array(
                'isRecursive' => true
            ))
        );

        $postRecursive = count($resourceRightsRepo->findRecursiveByResource($this->getDirectory('dir1')));
        $this->assertEquals(1, $postRecursive - $preRecursive);

        $this->assertRolePermissionsOnResource(
            $role,
            $this->getDirectory('dir1'),
            array('delete' => false, 'open' => false, 'export' => false, 'copy' => false, 'edit' => false),
            array('delete' => false, 'open' => false, 'export' => false, 'copy' => false, 'edit' => false)
        );
    }

    private function assertRolePermissionsOnResource(
        Role $role,
        AbstractResource $resource,
        array $expectedPermissionsOnResource,
        array $expectedPermissionsOnDescendants
    )
    {
        $rightsRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $resources = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findDescendants($resource, true);

        for ($i = 0, $resourceCount = count($resources); $i < $resourceCount; ++$i) {
            $expectedPermissions = $i === 0 ? $expectedPermissionsOnResource : $expectedPermissionsOnDescendants;
            $resourceRights = $rightsRepo->findOneBy(array('resource' => $resources[$i], 'role' => $role));
            $this->em->refresh($resourceRights);
            $this->assertEquals($expectedPermissions['open'], $resourceRights->canOpen());
            $this->assertEquals($expectedPermissions['delete'], $resourceRights->canDelete());
            $this->assertEquals($expectedPermissions['export'], $resourceRights->canExport());
            $this->assertEquals($expectedPermissions['edit'], $resourceRights->canEdit());
            $this->assertEquals($expectedPermissions['copy'], $resourceRights->canCopy());
        }
    }
}
