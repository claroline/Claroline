<?php

namespace Claroline\CoreBundle\Controller;

use \Mockery as m;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Doctrine\Common\Collections\ArrayCollection;

class AdministrationControllerTest extends MockeryTestCase
{
    private $userManager;
    private $roleManager;
    private $groupManager;
    private $workspaceManager;
    private $security;
    private $eventDispatcher;
    private $configHandler;
    private $formFactory;
    private $analyticsManager;
    private $translator;
    private $request;

    protected function setUp()
    {
        parent::setUp();
        $this->userManager = m::mock('Claroline\CoreBundle\Manager\UserManager');
        $this->roleManager = m::mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->groupManager = m::mock('Claroline\CoreBundle\Manager\GroupManager');
        $this->workspaceManager = m::mock('Claroline\CoreBundle\Manager\WorkspaceManager');
        $this->security = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->eventDispatcher = m::mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->configHandler = m::mock('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
        $this->formFactory = m::mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->analyticsManager = m::mock('Claroline\CoreBundle\Manager\AnalyticsManager');
        $this->translator = m::mock('Symfony\Component\Translation\Translator');
        $this->request = m::mock('Symfony\Component\HttpFoundation\Request');
    }

    public function testIndexAction()
    {
        $this->assertEquals(array(), $this->getController()->indexAction());
    }

    public function testUserCreationFormAction()
    {
        $user = new User();
        $roleA = new Role();
        $roleB = new Role();
        $roles = array($roleA, $roleB);
        $form = m::mock('Symfony\Component\Form\Form');

        $this->roleManager->shouldReceive('getPlatformRoles')
            ->with($user)
            ->once()
            ->andReturn($roles);
        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_USER, array($roles))
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');

        $this->assertEquals(
            array('form_complete_user' => 'view'),
            $this->getController()->userCreationFormAction($user)
        );
    }

    public function testCreateUserAction()
    {
        $controller = $this->getController(array('redirect', 'generateUrl'));
        $currentUser = new User();
        $user = new User();
        $roleA = new Role();
        $roleB = new Role();
        $roleC = new Role();
        $roleD = new Role();
        $roles = array($roleA, $roleB);
        $newRoles = new ArrayCollection(array($roleC, $roleD));
        $form = m::mock('Symfony\Component\Form\Form');
        $formInterface = m::mock('Symfony\Component\Form\FormInterface');

        $this->roleManager->shouldReceive('getPlatformRoles')
            ->with($currentUser)
            ->once()
            ->andReturn($roles);
        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_USER, array($roles))
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $form->shouldReceive('getData')
            ->once()
            ->andReturn($user);
        $form->shouldReceive('get')
            ->with('platformRoles')
            ->once()
            ->andReturn($formInterface);
        $formInterface->shouldReceive('getData')
            ->once()
            ->andReturn($newRoles);
        $this->userManager->shouldReceive('insertUserWithRoles')
            ->with($user, $newRoles)
            ->once();
        $controller->shouldReceive('generateUrl')
            ->with('claro_admin_user_list')
            ->once()
            ->andReturn('url');
        $controller->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals(
            'redirection',
            $controller->createUserAction($currentUser)
        );
    }

    public function testDeleteUsersAction()
    {
        $userA = new User();
        $userB = new User();
        $users = array($userA, $userB);

        $this->userManager->shouldReceive('deleteUser')
            ->with($userA)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogUserDelete', array($userA))
            ->once();
        $this->userManager->shouldReceive('deleteUser')
            ->with($userB)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogUserDelete', array($userB))
            ->once();

        $this->getController()->deleteUsersAction($users);
    }

    public function testUserListActionWithoutSearch()
    {
        $this->userManager->shouldReceive('getAllUsers')
            ->with(1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => ''),
            $this->getController()->userListAction(1, '')
        );
    }

    public function testUserListActionWithSearch()
    {
        $this->userManager->shouldReceive('getUsersByName')
            ->with('search', 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => 'search'),
            $this->getController()->userListAction(1, 'search')
        );
    }

    public function testGroupListActionWithoutSearch()
    {
        $this->groupManager->shouldReceive('getGroups')
            ->with(1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => ''),
            $this->getController()->groupListAction(1, '')
        );
    }

    public function testGroupListActionWithSearch()
    {
        $this->groupManager->shouldReceive('getGroupsByName')
            ->with('search', 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => 'search'),
            $this->getController()->groupListAction(1, 'search')
        );
    }

    public function testUsersOfGroupListActionWithoutSearch()
    {
        $group = new Group();

        $this->userManager->shouldReceive('getUsersByGroup')
            ->with($group, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => '', 'group' => $group),
            $this->getController()->usersOfGroupListAction($group, 1, '')
        );
    }

    public function testUsersOfGroupListActionWithSearch()
    {
        $group = new Group();

        $this->userManager->shouldReceive('getUsersByNameAndGroup')
            ->with('search', $group, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => 'search', 'group' => $group),
            $this->getController()->usersOfGroupListAction($group, 1, 'search')
        );
    }

    public function testOutsideOfGroupUserListActionWithoutSearch()
    {
        $group = new Group();

        $this->userManager->shouldReceive('getGroupOutsiders')
            ->with($group, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => '', 'group' => $group),
            $this->getController()->outsideOfGroupUserListAction($group, 1, '')
        );
    }

    public function testOutsideOfGroupUserListActionWithSearch()
    {
        $group = new Group();

        $this->userManager->shouldReceive('getGroupOutsidersByName')
            ->with($group, 'search', 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => 'search', 'group' => $group),
            $this->getController()->outsideOfGroupUserListAction($group, 1, 'search')
        );
    }

    public function testGroupCreationFormAction()
    {
        $form = m::mock('Symfony\Component\Form\Form');

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_GROUP)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');

        $this->assertEquals(
            array('form_group' => 'view'),
            $this->getController()->groupCreationFormAction()
        );
    }

    public function testCreateGroupAction()
    {
        $controller = $this->getController(array('redirect', 'generateUrl'));
        $form = m::mock('Symfony\Component\Form\Form');
        $group = m::mock('Claroline\CoreBundle\Entity\Group');
        $role = new Role();

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_GROUP, array())
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $form->shouldReceive('getData')
            ->once()
            ->andReturn($group);
        $this->roleManager->shouldReceive('getRoleByName')
            ->with('ROLE_USER')
            ->once()
            ->andReturn($role);
        $group->shouldReceive('setPlatformRole')
            ->with($role)
            ->once();
        $this->groupManager->shouldReceive('insertGroup')
            ->with($group)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupCreate', array($group))
            ->once();
        $controller->shouldReceive('generateUrl')
            ->with('claro_admin_group_list')
            ->once()
            ->andReturn('url');
        $controller->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals('redirection', $controller->createGroupAction());
    }

    public function testAddUsersToGroupAction()
    {
        $userA = new User();
        $userB = new User();
        $users = array($userA, $userB);
        $group = new Group();

        $this->groupManager->shouldReceive('addUsersToGroup')
            ->with($group, $users)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupAddUser', array($group, $userA))
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupAddUser', array($group, $userB))
            ->once();

        $this->getController()->addUsersToGroupAction($group, $users);
    }

    public function testDeleteUsersFromGroupAction()
    {
        $userA = new User();
        $userB = new User();
        $users = array($userA, $userB);
        $group = new Group();

        $this->groupManager->shouldReceive('removeUsersFromGroup')
            ->with($group, $users)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupRemoveUser', array($group, $userA))
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupRemoveUser', array($group, $userB))
            ->once();

        $this->getController()->deleteUsersFromGroupAction($group, $users);
    }

    public function testDeleteGroupsAction()
    {
        $groupA = new Group();
        $groupB = new Group();
        $groups = array($groupA, $groupB);

        $this->groupManager->shouldReceive('deleteGroup')
            ->with($groupA)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupDelete', array($groupA))
            ->once();
        $this->groupManager->shouldReceive('deleteGroup')
            ->with($groupB)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupDelete', array($groupB))
            ->once();

        $this->getController()->deleteGroupsAction($groups);
    }

    public function testGroupSettingsFormAction()
    {
        $group = new Group();
        $form = m::mock('Symfony\Component\Form\Form');

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_GROUP_SETTINGS, array(), $group)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('form');

        $this->assertEquals(
            array('group' => $group, 'form_settings' => 'form'),
            $this->getController()->groupSettingsFormAction($group)
        );
    }

    public function testUpdateGroupSettingsAction()
    {
        $controller = $this->getController(array('redirect', 'generateUrl'));
        $group = m::mock('Claroline\CoreBundle\Entity\Group');
        $newGroup = m::mock('Claroline\CoreBundle\Entity\Group');
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $form = m::mock('Symfony\Component\Form\Form');

        $group->shouldReceive('getPlatformRole')
            ->once()
            ->andReturn($role);
        $role->shouldReceive('getTranslationKey')
            ->once()
            ->andReturn('old_key');
        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_GROUP_SETTINGS, array(), $group)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $form->shouldReceive('getData')
            ->once()
            ->andReturn($newGroup);
        $this->groupManager->shouldReceive('updateGroup')
            ->with($newGroup, 'old_key')
            ->once();
        $controller->shouldReceive('generateUrl')
            ->with('claro_admin_group_list')
            ->once()
            ->andReturn('url');
        $controller->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals(
            'redirection',
            $controller->updateGroupSettingsAction($group)
        );
    }

    public function testPlatformSettingsFormAction()
    {
        $this->markTestSkipped('Unable to test because it uses getThemes which is a private function');
    }

    public function testUpdatePlatformSettingsAction()
    {
        $this->markTestSkipped('Unable to test because it uses getThemes which is a private function');
    }

    public function testPluginParametersAction()
    {
        $event = m::mock('Claroline\CoreBundle\Event\Event\PluginOptionsEvent');

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('plugin_options_domain', 'PluginOptions', array())
            ->once()
            ->andReturn($event);
        $event->shouldReceive('getResponse')
            ->once()
            ->andReturn('response');

        $this->assertEquals('response', $this->getController()->pluginParametersAction('domain'));
    }

    public function testUsersManagementAction()
    {
        $this->assertEquals(array(), $this->getController()->usersManagementAction());
    }

    public function testImportUsersFormAction()
    {
        $form = m::mock('Symfony\Component\Form\Form');

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_USER_IMPORT)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');

        $this->assertEquals(array('form' => 'view'), $this->getController()->importUsersFormAction());
    }

    public function testImportUsers()
    {
        $this->markTestSkipped('Refactoring may be needed');
    }

    public function testImportUsersIntoGroupFormAction()
    {
        $group = m::mock('Claroline\CoreBundle\Entity\Group');
        $form = m::mock('Symfony\Component\Form\Form');

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_USER_IMPORT)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');

        $this->assertEquals(
            array('form' => 'view', 'group' => $group),
            $this->getController()->importUsersIntoGroupFormAction($group)
        );
    }

    public function testImportUsersIntoGroupAction()
    {
        $this->markTestSkipped('Refactoring may be needed');
    }

    private function getController(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {

            return new AdministrationController(
                $this->userManager,
                $this->roleManager,
                $this->groupManager,
                $this->workspaceManager,
                $this->security,
                $this->eventDispatcher,
                $this->configHandler,
                $this->formFactory,
                $this->analyticsManager,
                $this->translator,
                $this->request
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return m::mock(
            'Claroline\CoreBundle\Controller\AdministrationController' . $stringMocked,
            array(
                $this->userManager,
                $this->roleManager,
                $this->groupManager,
                $this->workspaceManager,
                $this->security,
                $this->eventDispatcher,
                $this->configHandler,
                $this->formFactory,
                $this->analyticsManager,
                $this->translator,
                $this->request
            )
        );
    }
}