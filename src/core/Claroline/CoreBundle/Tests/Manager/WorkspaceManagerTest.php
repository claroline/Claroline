<?php

namespace Claroline\CoreBundle\Manager;

use Mockery as m;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use org\bovigo\vfs\vfsStream;

class WorkspaceManagerTest extends MockeryTestCase
{
    private $roleManager;
    private $resourceManager;
    private $toolManager;
    private $orderedToolRepo;
    private $resourceNodeRepo;
    private $resourceRightsRepo;
    private $resourceTypeRepo;
    private $roleRepo;
    private $workspaceRepo;
    private $dispatcher;
    private $om;
    private $ut;
    private $templateDir;
    private $pagerFactory;

    public function setUp()
    {
        parent::setUp();

        vfsStream::setup('template');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->toolManager = $this->mock('Claroline\CoreBundle\Manager\ToolManager');
        $this->resourceManager = $this->mock('Claroline\CoreBundle\Manager\ResourceManager');
        $this->orderedToolRepo = $this->mock('Claroline\CoreBundle\Repository\OrderedToolRepository');
        $this->resourceNodeRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceNodeRepository');
        $this->resourceRightsRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->resourceTypeRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
        $this->roleRepo = $this->mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->rightsRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->workspaceRepo = $this->mock('Claroline\CoreBundle\Repository\AbstractResourceRepository');
        $this->dispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->ut = $this->mock('Claroline\CoreBundle\Library\Utilities\ClaroUtilities');
        $this->templateDir = vfsStream::url('template');
        $this->pagerFactory = $this->mock('Claroline\CoreBundle\Pager\PagerFactory');
    }

    public function testCreate()
    {
        $wManager = $this->getManager(array('prepareRightsArray'));

        $toolsInfos = array(
            'toolName1' => array(
                'perms' => array('ROLE_WS_MANAGER', 'ROLE_WS_VISITOR'),
                'name' => 'orderedToolName1'
            ),
            'toolName2' => array(
                'perms' => array('ROLE_WS_MANAGER', 'ROLE_WS_VISITOR'),
                'name' => 'orderedToolName2'
            )
        );

        $tools = array(
            'tools' => array(
                'toolName1' => array(
                    'config' => 'config',
                    'files' => array('file1')
                )
            )
        );

        $config = $this->mock('Claroline\CoreBundle\Library\Workspace\Configuration');
        $config->shouldReceive('getWorkspaceName')->once()->andReturn('wsname');
        $config->shouldReceive('isPublic')->once()->andReturn(true);
        $config->shouldReceive('getWorkspaceCode')->once()->andReturn('wscode');
        $config->shouldReceive('getRoles')->once()->andReturn(array('ROLE_WS_MANAGER', 'ROLE_WS_VISITOR'));
        $config->shouldReceive('getPermsRootConfiguration')->once()->andReturn(array('rootconfig'));
        $config->shouldReceive('getToolsConfiguration')->once()->andReturn($tools);
        $config->shouldReceive('getToolsPermissions')->once()->andReturn($toolsInfos);
        $config->shouldReceive('getArchive')->times(2)->andReturn(new \ZipArchive());
        $config->shouldReceive('check')->once();
        $config->shouldReceive('isDisplayable')->once()->andReturn('displayable');
        $this->ut->shouldReceive('generateGuid')->once()->andReturn('guid');

        $manager = $this->mock('Claroline\CoreBundle\Entity\User');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace');
        $workspace->shouldReceive('setName')->once()->with('wsname');
        $workspace->shouldReceive('setPublic')->once()->with(true);
        $workspace->shouldReceive('setCode')->once()->with('wscode');
        $workspace->shouldReceive('setGuid')->once()->with('guid');
        $workspace->shouldReceive('setDisplayable')->once()->with('displayable');
        $workspace->shouldReceive('getName')->once()->andReturn('wsname');
        $workspace->shouldReceive('getCode')->once()->andReturn('wscode');

        $roleManager = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleVisitor = $this->mock('Claroline\CoreBundle\Entity\Role');
        $anon = $this->mock('Claroline\CoreBundle\Entity\Role');
        $baseRoles = array('ROLE_WS_MANAGER' => $roleManager, 'ROLE_WS_VISITOR' => $roleVisitor);

        $dirType = new \Claroline\CoreBundle\Entity\Resource\ResourceType();
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->resourceTypeRepo->shouldReceive('findOneByName')->once()->with('directory')->andReturn($dirType);

        $dir = $this->mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $tool = new \Claroline\CoreBundle\Entity\Tool\Tool();

        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('factory')->once()
            ->with('Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace')
            ->andReturn($workspace);
        $this->roleManager->shouldReceive('initWorkspaceBaseRole')->once()
            ->with(array('ROLE_WS_MANAGER', 'ROLE_WS_VISITOR'), $workspace)->andReturn($baseRoles);
        $this->roleRepo->shouldReceive('findOneBy')
            ->with(array('name' => 'ROLE_ANONYMOUS'))->once()->andReturn($anon);
        $this->roleManager->shouldReceive('associateRole')->once()->with($manager, $roleManager);
        $this->om->shouldReceive('factory')->once()
            ->with('Claroline\CoreBundle\Entity\Resource\Directory')->andReturn($dir);
        $dir->shouldReceive('setName')->with('wsname - wscode')->once();
        $wManager->shouldReceive('prepareRightsArray')->once()
            ->with(array('rootconfig'), m::any())->andReturn(array('preprights'));

        $this->resourceManager->shouldReceive('create')->once()
            ->with($dir, $dirType, $manager, $workspace, null, null, array('preprights'))->andReturn($dir);
        $this->toolManager->shouldReceive('getOneToolByName')->once()->with('toolName1')->andReturn($tool);
        $this->toolManager->shouldReceive('getOneToolByName')->once()->with('toolName2')->andReturn($tool);
        $this->toolManager->shouldReceive('import');

        $this->dispatcher->shouldReceive('dispatch')->once()
            ->with('log', 'Log\LogWorkspaceCreate', array($workspace));

        $this->om->shouldReceive('persist')->once()->with($workspace);
        $this->om->shouldReceive('endFlushSuite')->once();
        $this->assertEquals($workspace, $wManager->create($config, $manager));
    }

    public function testCreateWorkspace()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $this->om->shouldReceive('persist')->once()->with($workspace);
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->createWorkspace($workspace);
    }

    public function testDeleteWorkspace()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $this->om->shouldReceive('remove')->once()->with($workspace);
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->deleteWorkspace($workspace);
    }

    public function testExport()
    {
        $manager = $this->getManager(
            array(
                'createArchive',
                'exportRolesSection',
                'exportRootPermsSection',
                'exportToolsInfosSection',
                'exportToolsSection'
            )
        );

        $workspace = new \Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
        $configName = 'configname';
        $archive = $this->mock('ZipArchive');

        $this->om->shouldReceive('startFlushSuite')->once();
        $manager->shouldReceive('createArchive')->once()->andReturn($archive);
        $manager->shouldReceive('exportRolesSection')->once()->andReturn(array());
        $manager->shouldReceive('exportRootPermsSection')->once()->andReturn(array());
        $manager->shouldReceive('exportToolsInfosSection')->once()->andReturn(array());
        $manager->shouldReceive('exportToolsSection')->once()->andReturn(array());
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
        $archive->shouldReceive('open')->once()->with($this->templateDir. 'guid.zip', \ZipArchive::CREATE);
        $this->assertEquals($archive, $this->getManager()->createArchive('config'));
    }

    public function testExportRoleSection()
    {
        $expectedResult = array();
        $expectedResult['roles']['ROLE_WS_TEST1'] = 'translationrole1';
        $expectedResult['roles']['ROLE_WS_TEST2'] = 'translationrole2';

        $workspace = new \Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace();
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
            ->with($workspace)->andReturn(array($roleA, $roleB));

         $this->assertEquals($expectedResult, $this->getManager()->exportRolesSection($workspace));
    }

    public function testExportRootPermsSection()
    {
        $perms = array(
            'canCopy' => true,
            'canOpen' => true,
            'canDelete' => false,
            'canExport' => false,
            'canEdit' => false
        );

        $creations = array(
            'name' => 'directory'
        );

        $expectedResult = array(
            "root_perms" => array(
                    'ROLE_WS_TEST1' => array(
                        'canCopy' => true,
                        'canOpen' => true,
                        'canDelete' => false,
                        'canExport' => false,
                        'canEdit' => false,
                        'canCreate' => $creations
                    ),
                    'ROLE_WS_TEST2' => array(
                        'canCopy' => true,
                        'canOpen' => true,
                        'canDelete' => false,
                        'canExport' => false,
                        'canEdit' => false,
                        'canCreate' => array()
                    )
                )
        );

        $workspace = new \Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace();
        $root = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleA->shouldReceive('getName')->andReturn('ROLE_WS_TEST1_AAA');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB->shouldReceive('getName')->andReturn('ROLE_WS_TEST2_AAA');
        $this->roleRepo->shouldReceive('findByWorkspace')->once()
            ->with($workspace)->andReturn(array($roleA, $roleB));
        $this->resourceNodeRepo->shouldReceive('findWorkspaceRoot')->once()->with($workspace)->andReturn($root);
        $this->roleManager->shouldReceive('getRoleBaseName')->once()
            ->with('ROLE_WS_TEST1_AAA')->andReturn('ROLE_WS_TEST1');
        $this->roleManager->shouldReceive('getRoleBaseName')->once()
            ->with('ROLE_WS_TEST2_AAA')->andReturn('ROLE_WS_TEST2');
        $this->resourceRightsRepo->shouldReceive('findMaximumRights')
            ->once()->with(array('ROLE_WS_TEST1_AAA'), $root)->andReturn($perms);
        $this->resourceRightsRepo->shouldReceive('findMaximumRights')
            ->once()->with(array('ROLE_WS_TEST2_AAA'), $root)->andReturn($perms);
        $this->resourceRightsRepo->shouldReceive('findCreationRights')
            ->once()->with(array('ROLE_WS_TEST1_AAA'), $root)->andReturn($creations);
        $this->resourceRightsRepo->shouldReceive('findCreationRights')
            ->once()->with(array('ROLE_WS_TEST2_AAA'), $root)->andReturn(array());

        $result = $this->getManager()->exportRootPermsSection($workspace);
        $this->assertEquals($expectedResult, $result);
    }

    public function testExportToolsInfosSection()
    {
        $expected = array(
            'tools_infos' =>
                array(
                    'toolName1' => array(
                        'perms' => array('ROLE_WS_TEST1', 'ROLE_WS_TEST2'),
                        'name' => 'orderedToolName1'
                    ),
                    'toolName2' => array(
                        'perms' => array('ROLE_WS_TEST1', 'ROLE_WS_TEST2'),
                        'name' => 'orderedToolName2'
                    )
                )
        );

        $workspace = new \Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace();
        $wotA = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $wotB = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $toolA = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $toolB = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');

        $roleA->shouldReceive('getName')->andReturn('ROLE_WS_TEST1_AAA');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB->shouldReceive('getName')->andReturn('ROLE_WS_TEST2_AAA');

        $roles = array($roleA, $roleB);
        $wots = array($wotA, $wotB);

        $wotA->shouldReceive('getTool')->once()->andReturn($toolA);
        $wotB->shouldReceive('getTool')->once()->andReturn($toolB);
        $wotA->shouldReceive('getName')->once()->andReturn('orderedToolName1');
        $wotB->shouldReceive('getName')->once()->andReturn('orderedToolName2');
        $toolA->shouldReceive('getName')->once()->andReturn('toolName1');
        $toolB->shouldReceive('getName')->once()->andReturn('toolName2');

        $this->orderedToolRepo->shouldReceive('findBy')->once()
            ->with(array('workspace' => $workspace), array('order' => 'ASC'))->andReturn($wots);
        $this->roleRepo->shouldReceive('findByWorkspaceAndTool')->andReturn($roles)->times(2);

        $this->roleManager->shouldReceive('getRoleBaseName')->with('ROLE_WS_TEST1_AAA')->andReturn('ROLE_WS_TEST1');
        $this->roleManager->shouldReceive('getRoleBaseName')->with('ROLE_WS_TEST2_AAA')->andReturn('ROLE_WS_TEST2');

        $this->assertEquals($expected, $this->getManager()->exportToolsInfosSection($workspace));
    }

    public function testExportToolsSection()
    {
        $expected = array(
            'tools' => array(
                'toolName1' => array(
                    'config' => 'config',
                    'files' => array('file1')
                )
            )
        );

        $workspace = new \Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace();
        $archive = $this->mock('ZipArchive');

        $wotA = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $wotB = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $wots = array($wotA, $wotB);
        $toolA = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $toolB = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $wotA->shouldReceive('getTool')->andReturn($toolA);
        $wotB->shouldReceive('getTool')->andReturn($toolB);
        $toolA->shouldReceive('getName')->andReturn('toolName1');
        $toolB->shouldReceive('getName')->andReturn('toolName2');
        $toolA->shouldReceive('isExportable')->once()->andReturn(true);
        $toolB->shouldReceive('isExportable')->once()->andReturn(false);
        $event = $this->mock('Claroline\CoreBundle\Event\Event\ExportToolEvent');

        $this->orderedToolRepo->shouldReceive('findBy')->once()
            ->with(array('workspace' => $workspace), array('order' => 'ASC'))->andReturn($wots);

        $this->dispatcher->shouldReceive('dispatch')->once()
            ->with('tool_toolName1_to_template', 'ExportTool', array($workspace))->andReturn($event);

        $event->shouldReceive('getConfig')->andReturn(array('config' => 'config'));
        $event->shouldReceive('getFilenamesFromArchive')->andReturn(array('file1'));
        $event->shouldReceive('getFiles')
            ->andReturn(array(array('original_path' => 'path/original', 'archive_path' => 'file1')));
        $archive->shouldReceive('addFile')->with('path/original', 'file1');
        $this->assertEquals($expected, $this->getManager()->exportToolsSection($workspace, $archive));
    }

    public function testGetWorkspacesByUser()
    {
        $workspaces = array('workspaceA', 'workspaceB');
        $user = new User();

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findByUser')
            ->with($user)
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals($workspaces, $this->getManager()->getWorkspacesByUser($user));
    }

    public function testGetNonPersonalWorkspaces()
    {
        $workspaces = array('workspaceA', 'workspaceB');

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findNonPersonal')
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals($workspaces, $this->getManager()->getNonPersonalWorkspaces());
    }

    public function testGetWorkspacesByAnonymous()
    {
        $workspaces = array('workspaceA', 'workspaceB');

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
        $roles = array($roleA, $roleB);
        $workspaces = array('workspaceA', 'workspaceB');

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findByRoles')
            ->with($roles)
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals($workspaces, $this->getManager()->getWorkspacesByRoles($roles));
    }

    public function testGetWorkspaceIdsByUserAndRoleNames()
    {
        $roleNames = array('ROLE_A', 'ROLE_B');
        $user = new User();
        $workspaceIds = array(1, 2, 3);

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
        $roleNames = array('ROLE_A', 'ROLE_B');
        $user = new User();
        $workspaces = array('workspaceA', 'workspaceB');

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
        $roleNames = array('ROLE_A', 'ROLE_B');
        $user = new User();
        $workspaces = array('workspaceA', 'workspaceB');

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
        $roles = array($roleA, $roleB);
        $workspaces = array('workspaceA', 'workspaceB');

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
        $workspaces = array('workspaceA', 'workspaceB');

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
        $workspaces = array('workspaceA', 'workspaceB');

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

    public function testGetWorkspacesWithSelfRegistration()
    {
        $workspaces = array('workspaceA', 'workspaceB');

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->workspaceRepo->shouldReceive('findWorkspacesWithSelfRegistration')
            ->once()
            ->andReturn($workspaces);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(
            $workspaces,
            $this->getManager()->getWorkspacesWithSelfRegistration()
        );
    }

    public function testGetDisplayableWorkspacesBySearch()
    {
        $workspaces = array('workspaceA', 'workspaceB');

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
        $workspaceA = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $workspaceB = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $workspaces = array($workspaceA, $workspaceB);

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

    private function getManager(array $mockedMethods = array())
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
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->andReturn($this->workspaceRepo);

        if (count($mockedMethods) === 0) {
            return new WorkspaceManager(
                $this->roleManager,
                $this->resourceManager,
                $this->toolManager,
                $this->dispatcher,
                $this->om,
                $this->ut,
                $this->templateDir,
                $this->pagerFactory
            );
        } else {
            $stringMocked = '[';
                $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return $this->mock(
                'Claroline\CoreBundle\Manager\WorkspaceManager' . $stringMocked,
                array(
                    $this->roleManager,
                    $this->resourceManager,
                    $this->toolManager,
                    $this->dispatcher,
                    $this->om,
                    $this->ut,
                    $this->templateDir,
                    $this->pagerFactory
                )
            );
        }
    }
}
