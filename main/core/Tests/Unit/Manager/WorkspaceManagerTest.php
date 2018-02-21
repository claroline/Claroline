<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Mockery as m;
use org\bovigo\vfs\vfsStream;

class WorkspaceManagerTest extends MockeryTestCase
{
    private $roleManager;
    private $maskManager;
    private $resourceManager;
    private $toolManager;
    private $orderedToolRepo;
    private $resourceNodeRepo;
    private $resourceRightsRepo;
    private $resourceTypeRepo;
    private $roleRepo;
    private $workspaceRepo;
    private $strictDispatcher;
    private $om;
    private $ut;
    private $templateDir;
    private $pagerFactory;
    private $homeTabManager;
    private $workpaceFavouriteRepo;
    private $security;

    public function setUp()
    {
        parent::setUp();

        vfsStream::setup('template');
        $this->homeTabManager = $this->mock('Claroline\CoreBundle\Manager\HomeTabManager');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->toolManager = $this->mock('Claroline\CoreBundle\Manager\ToolManager');
        $this->resourceManager = $this->mock('Claroline\CoreBundle\Manager\ResourceManager');
        $this->orderedToolRepo = $this->mock('Claroline\CoreBundle\Repository\OrderedToolRepository');
        $this->resourceNodeRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceNodeRepository');
        $this->resourceRightsRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->resourceTypeRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
        $this->workspaceFavouriteRepo = $this->mock('ClarolineCoreBundle\Repository\WorkspaceFavouriteRepository');
        $this->userRepo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->roleRepo = $this->mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->workspaceRepo = $this->mock('Claroline\CoreBundle\Repository\WorkspaceRepository');
        $this->rightsRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->strictDispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->ut = $this->mock('Claroline\CoreBundle\Library\Utilities\ClaroUtilities');
        $this->templateDir = vfsStream::url('template');
        $this->pagerFactory = $this->mock('Claroline\CoreBundle\Pager\PagerFactory');
        $this->maskManager = $this->mock('Claroline\CoreBundle\Manager\MaskManager');
        $this->security = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
    }

    public function testCreateWorkspace()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $this->om->shouldReceive('persist')->once()->with($workspace);
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->createWorkspace($workspace);
    }

    public function testDeleteWorkspace()
    {
        $root = new ResourceNode();
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $this->resourceManager->shouldReceive('getWorkspaceRoot')->andReturn($root);
        $this->resourceManager->shouldReceive('delete')->with($root)->once();
        $this->om->shouldReceive('remove')->once()->with($workspace);
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->deleteWorkspace($workspace);
    }

    public function testExport()
    {
        $manager = $this->getManager(
            [
                'createArchive',
                'exportRolesSection',
                'exportRootPermsSection',
                'exportToolsInfosSection',
                'exportToolsSection',
            ]
        );

        $workspace = new Workspace();
        $configName = 'configname';
        $archive = $this->mock('ZipArchive');

        $this->om->shouldReceive('startFlushSuite')->once();
        $manager->shouldReceive('createArchive')->once()->andReturn($archive);
        $manager->shouldReceive('exportRolesSection')->once()->andReturn([]);
        $manager->shouldReceive('exportRootPermsSection')->once()->andReturn([]);
        $manager->shouldReceive('exportToolsInfosSection')->once()->andReturn([]);
        $manager->shouldReceive('exportToolsSection')->once()->andReturn([]);
        $archive->shouldReceive('addFromString')->once();
        $archive->shouldReceive('close')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $manager->export($workspace, $configName);
        $this->markTestIncomplete('How to make an assertion on description ?');
    }

    public function testCreateArchive()
    {
        $archive = $this->mock('ZipArchive');
        $this->om->shouldReceive('factory')->once()->with('\ZipArchive')->andReturn($archive);
        $this->ut->shouldReceive('generateGuid')->once()->andReturn('guid');
        $template = $this->mock('Claroline\CoreBundle\Entity\Workspace\Template');
        $this->om->shouldReceive('factory')->once()
            ->with('Claroline\CoreBundle\Entity\Workspace\Template')->andReturn($template);
        $template->shouldReceive('setHash')->once()->with('guid.zip');
        $template->shouldReceive('setName')->once()->with('config');
        $this->om->shouldReceive('persist')->once()->with($template);
        $this->om->shouldReceive('flush')->once();
        $archive->shouldReceive('open')->once()->with($this->templateDir.'guid.zip', \ZipArchive::CREATE);
        $this->assertEquals($archive, $this->getManager()->createArchive('config'));
    }

    public function testExportRoleSection()
    {
        $expectedResult = [];
        $expectedResult['roles']['ROLE_WS_TEST1'] = 'translationrole1';
        $expectedResult['roles']['ROLE_WS_TEST2'] = 'translationrole2';

        $workspace = new Workspace();
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleA->shouldReceive('getName')->once()->andReturn('ROLE_WS_TEST1_AAA');
        $roleA->shouldReceive('getTranslationKey')->once()->andReturn('translationrole1');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB->shouldReceive('getName')->once()->andReturn('ROLE_WS_TEST2_AAA');
        $roleB->shouldReceive('getTranslationKey')->once()->andReturn('translationrole2');
        $this->roleManager->shouldReceive('getRoleBaseName')->once()
            ->with('ROLE_WS_TEST1_AAA')->andReturn('ROLE_WS_TEST1');
        $this->roleManager->shouldReceive('getRoleBaseName')->once()
            ->with('ROLE_WS_TEST2_AAA')->andReturn('ROLE_WS_TEST2');
        $this->roleRepo->shouldReceive('findByWorkspace')->once()
            ->with($workspace)->andReturn([$roleA, $roleB]);

        $this->assertEquals($expectedResult, $this->getManager()->exportRolesSection($workspace));
    }

    public function testExportRootPermsSection()
    {
        $perms = [
            'copy' => true,
            'open' => true,
            'delete' => false,
            'export' => false,
            'edit' => false,
        ];

        $creations = [
            'name' => 'directory',
        ];

        $expectedResult = [
            'root_perms' => [
                    'ROLE_WS_TEST1' => [
                        'copy' => true,
                        'open' => true,
                        'delete' => false,
                        'export' => false,
                        'edit' => false,
                        'create' => $creations,
                    ],
                    'ROLE_WS_TEST2' => [
                        'copy' => true,
                        'open' => true,
                        'delete' => false,
                        'export' => false,
                        'edit' => false,
                        'create' => [],
                    ],
                ],
        ];

        $workspace = new Workspace();
        $resourceType = new ResourceType();
        $root = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $root->shouldReceive('getResourceType')->andReturn($resourceType);
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleA->shouldReceive('getName')->andReturn('ROLE_WS_TEST1_AAA');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB->shouldReceive('getName')->andReturn('ROLE_WS_TEST2_AAA');
        $this->roleRepo->shouldReceive('findByWorkspace')->once()
            ->with($workspace)->andReturn([$roleA, $roleB]);
        $this->resourceNodeRepo->shouldReceive('findWorkspaceRoot')->once()->with($workspace)->andReturn($root);
        $this->roleManager->shouldReceive('getRoleBaseName')->once()
            ->with('ROLE_WS_TEST1_AAA')->andReturn('ROLE_WS_TEST1');
        $this->roleManager->shouldReceive('getRoleBaseName')->once()
            ->with('ROLE_WS_TEST2_AAA')->andReturn('ROLE_WS_TEST2');
        $this->resourceRightsRepo->shouldReceive('findMaximumRights')->andReturn('123')
            ->once()->with(['ROLE_WS_TEST1_AAA'], $root)->andReturn('123');
        $this->resourceRightsRepo->shouldReceive('findMaximumRights')
            ->once()->with(['ROLE_WS_TEST2_AAA'], $root)->andReturn('123');
        $this->resourceRightsRepo->shouldReceive('findCreationRights')
            ->once()->with(['ROLE_WS_TEST1_AAA'], $root)->andReturn($creations);
        $this->resourceRightsRepo->shouldReceive('findCreationRights')
            ->once()->with(['ROLE_WS_TEST2_AAA'], $root)->andReturn([]);
        $this->maskManager->shouldReceive('decodeMask')->with(m::any(), $resourceType)->andReturn($perms);

        $result = $this->getManager()->exportRootPermsSection($workspace);
        $this->assertEquals($result, $expectedResult);
    }

    public function testExportToolsInfosSection()
    {
        $expected = [
            'tools_infos' => [
                    'toolName1' => [
                        'perms' => ['ROLE_WS_TEST1', 'ROLE_WS_TEST2'],
                        'name' => 'orderedToolName1',
                    ],
                    'toolName2' => [
                        'perms' => ['ROLE_WS_TEST1', 'ROLE_WS_TEST2'],
                        'name' => 'orderedToolName2',
                    ],
                ],
        ];

        $workspace = new Workspace();
        $wotA = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $wotB = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $toolA = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $toolB = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');

        $roleA->shouldReceive('getName')->andReturn('ROLE_WS_TEST1_AAA');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB->shouldReceive('getName')->andReturn('ROLE_WS_TEST2_AAA');

        $roles = [$roleA, $roleB];
        $wots = [$wotA, $wotB];

        $wotA->shouldReceive('getTool')->once()->andReturn($toolA);
        $wotB->shouldReceive('getTool')->once()->andReturn($toolB);
        $wotA->shouldReceive('getName')->once()->andReturn('orderedToolName1');
        $wotB->shouldReceive('getName')->once()->andReturn('orderedToolName2');
        $toolA->shouldReceive('getName')->once()->andReturn('toolName1');
        $toolB->shouldReceive('getName')->once()->andReturn('toolName2');

        $this->orderedToolRepo->shouldReceive('findBy')->once()
            ->with(['workspace' => $workspace], ['order' => 'ASC'])->andReturn($wots);
        $this->roleRepo->shouldReceive('findByWorkspaceAndTool')->andReturn($roles)->times(2);

        $this->roleManager->shouldReceive('getRoleBaseName')->with('ROLE_WS_TEST1_AAA')->andReturn('ROLE_WS_TEST1');
        $this->roleManager->shouldReceive('getRoleBaseName')->with('ROLE_WS_TEST2_AAA')->andReturn('ROLE_WS_TEST2');

        $this->assertEquals($expected, $this->getManager()->exportToolsInfosSection($workspace));
    }

    public function testExportToolsSection()
    {
        $expected = [
            'tools' => [
                'toolName1' => [
                    'config' => 'config',
                    'files' => ['file1'],
                ],
            ],
        ];

        $workspace = new Workspace();
        $archive = $this->mock('ZipArchive');

        $wotA = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $wotB = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $wots = [$wotA, $wotB];
        $toolA = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $toolB = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $wotA->shouldReceive('getTool')->andReturn($toolA);
        $wotB->shouldReceive('getTool')->andReturn($toolB);
        $toolA->shouldReceive('getName')->andReturn('toolName1');
        $toolB->shouldReceive('getName')->andReturn('toolName2');
        $toolA->shouldReceive('isExportable')->once()->andReturn(true);
        $toolB->shouldReceive('isExportable')->once()->andReturn(false);
        $event = $this->mock('Claroline\CoreBundle\Event\ExportToolEvent');

        $this->orderedToolRepo->shouldReceive('findBy')->once()
            ->with(['workspace' => $workspace], ['order' => 'ASC'])->andReturn($wots);

        $this->strictDispatcher->shouldReceive('dispatch')->once()
            ->with('tool_toolName1_to_template', 'ExportTool', [$workspace])->andReturn($event);

        $event->shouldReceive('getConfig')->andReturn(['config' => 'config']);
        $event->shouldReceive('getFilenamesFromArchive')->andReturn(['file1']);
        $event->shouldReceive('getFiles')
            ->andReturn([['original_path' => 'path/original', 'archive_path' => 'file1']]);
        $archive->shouldReceive('addFile')->with('path/original', 'file1');
        $this->assertEquals($expected, $this->getManager()->exportToolsSection($workspace, $archive));
    }

    public function testGetWorkspacesByUser()
    {
        $workspaces = ['workspaceA', 'workspaceB'];
        $user = new User();

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findByUser')
            ->with($user)
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals($workspaces, $this->getManager()->getWorkspacesByUser($user));
    }

    public function testGetWorkspacesByAnonymous()
    {
        $workspaces = ['workspaceA', 'workspaceB'];

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findByAnonymous')
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals($workspaces, $this->getManager()->getWorkspacesByAnonymous());
    }

    public function testGetNbWorkspaces()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('count')
            ->once()
            ->andReturn(4);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(4, $this->getManager()->getNbWorkspaces());
    }

    public function testGetWorkspacesByRoles()
    {
        $roleA = new Role();
        $roleB = new Role();
        $roles = [$roleA, $roleB];
        $workspaces = ['workspaceA', 'workspaceB'];

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findByRoles')
            ->with($roles)
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals($workspaces, $this->getManager()->getOpenableWorkspacesByRoles($roles));
    }

    public function testGetWorkspaceIdsByUserAndRoleNames()
    {
        $roleNames = ['ROLE_A', 'ROLE_B'];
        $user = new User();
        $workspaceIds = [1, 2, 3];

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findIdsByUserAndRoleNames')
            ->with($user, $roleNames)
            ->once()
            ->andReturn($workspaceIds);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(
            $workspaceIds,
            $this->getManager()->getWorkspaceIdsByUserAndRoleNames($user, $roleNames)
        );
    }

    public function testGetWorkspacesByUserAndRoleNames()
    {
        $roleNames = ['ROLE_A', 'ROLE_B'];
        $user = new User();
        $workspaces = ['workspaceA', 'workspaceB'];

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findByUserAndRoleNames')
            ->with($user, $roleNames)
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(
            $workspaces,
            $this->getManager()->getWorkspacesByUserAndRoleNames($user, $roleNames)
        );
    }

    public function testGetWorkspacesByUserAndRoleNamesNotIn()
    {
        $roleNames = ['ROLE_A', 'ROLE_B'];
        $user = new User();
        $workspaces = ['workspaceA', 'workspaceB'];

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findByUserAndRoleNamesNotIn')
            ->with($user, $roleNames, null)
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(
            $workspaces,
            $this->getManager()->getWorkspacesByUserAndRoleNamesNotIn($user, $roleNames)
        );
    }

    public function testGetLatestWorkspacesByUser()
    {
        $user = new User();
        $roleA = new Role();
        $roleB = new Role();
        $roles = [$roleA, $roleB];
        $workspaces = ['workspaceA', 'workspaceB'];

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findLatestWorkspacesByUser')
            ->with($user, $roles, 5)
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(
            $workspaces,
            $this->getManager()->getLatestWorkspacesByUser($user, $roles, 5)
        );
    }

    public function testGetWorkspacesWithMostResources()
    {
        $workspaces = ['workspaceA', 'workspaceB'];

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findWorkspacesWithMostResources')
            ->with(5)
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(
            $workspaces,
            $this->getManager()->getWorkspacesWithMostResources(5)
        );
    }

    public function testGetWorkspaceById()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn('workspace');
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(
            'workspace',
            $this->getManager()->getWorkspaceById(1)
        );
    }

    public function testGetOneByGuid()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findOneByGuid')
            ->with(1)
            ->once()
            ->andReturn('workspace');
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(
            'workspace',
            $this->getManager()->getOneByGuid(1)
        );
    }

    public function testGetDisplayableWorkspaces()
    {
        $workspaces = ['workspaceA', 'workspaceB'];

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findDisplayableWorkspaces')
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(
            $workspaces,
            $this->getManager()->getDisplayableWorkspaces()
        );
    }

    public function testGetDisplayableWorkspacesBySearch()
    {
        $workspaces = ['workspaceA', 'workspaceB'];

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findDisplayableWorkspacesBySearch')
            ->with('search')
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(
            $workspaces,
            $this->getManager()->getDisplayableWorkspacesBySearch('search')
        );
    }

    public function testGetDisplayableWorkspacesBySearchPager()
    {
        $workspaceA = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaceB = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaces = [$workspaceA, $workspaceB];

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo
            ->shouldReceive('findDisplayableWorkspacesBySearch')
            ->with('search')
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);
        $this->pagerFactory
            ->shouldReceive('createPagerFromArray')
            ->with($workspaces, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            'pager',
            $this->getManager()->getDisplayableWorkspacesBySearchPager('search', 1)
        );
    }

    public function testAddUserAction()
    {
        $user = new User();
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $role = new Role();
        $userManager = $this->mock('Claroline\CoreBundle\Manager\UserManager');

        $this->roleManager
            ->shouldReceive('getCollaboratorRole')
            ->with($workspace)
            ->once()
            ->andReturn($role);
        $this->roleManager
            ->shouldReceive('getWorkspaceRolesForUser')
            ->with($user, $workspace)
            ->once()
            ->andReturn([]);
        $this->roleManager
            ->shouldReceive('associateRole')
            ->with($user, $role)
            ->once();
        $this->strictDispatcher
            ->shouldReceive('dispatch')
            ->with(
                'log',
                'Log\LogRoleSubscribe',
                [$role, $user]
            )
            ->once();
        $this->security->shouldReceive('setToken')
            ->with(anInstanceOf('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken'))
            ->once();
        $userManager
            ->shouldReceive('convertUsersToArray')
            ->with([$user])
            ->once()
            ->andReturn(['user' => 'user']);

        $this->assertEquals(
            $user,
            $this->getManager()->addUserAction($workspace, $user)
        );
    }

    private function getManager(array $mockedMethods = [])
    {
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceType')
            ->andReturn($this->resourceTypeRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceNode')
            ->andReturn($this->resourceNodeRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Tool\OrderedTool')
            ->andReturn($this->orderedToolRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Role')
            ->andReturn($this->roleRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceRights')
            ->andReturn($this->resourceRightsRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Workspace\Workspace')
            ->andReturn($this->workspaceRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:User')
            ->andReturn($this->userRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Workspace\WorkspaceFavourite')
            ->andReturn($this->userRepo);

        if (0 === count($mockedMethods)) {
            return new WorkspaceManager(
                $this->homeTabManager,
                $this->roleManager,
                $this->maskManager,
                $this->resourceManager,
                $this->toolManager,
                $this->strictDispatcher,
                $this->om,
                $this->ut,
                $this->templateDir,
                $this->pagerFactory,
                $this->security
            );
        } else {
            $stringMocked = '[';
            $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return $this->mock(
                'Claroline\CoreBundle\Manager\WorkspaceManager'.$stringMocked,
                [
                    $this->homeTabManager,
                    $this->roleManager,
                    $this->maskManager,
                    $this->resourceManager,
                    $this->toolManager,
                    $this->strictDispatcher,
                    $this->om,
                    $this->ut,
                    $this->templateDir,
                    $this->pagerFactory,
                    $this->security,
                ]
            );
        }
    }
}
