<?php

namespace Claroline\CoreBundle\Controller\Tool;

use \Mockery as m;
//use Claroline\CoreBundle\Entity\Group;
//use Claroline\CoreBundle\Entity\Role;
//use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\HttpFoundation\RedirectResponse;
//use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\User;
//use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
//use Doctrine\Common\Collections\ArrayCollection;

class UserControllerTest extends MockeryTestCase
{
    private $userManager;
    private $roleManager;
    private $workspaceTagManager;
    private $eventDispatcher;
    private $pagerFactory;
    private $security;
    private $router;

    protected function setUp()
    {
        parent::setUp();
        $this->userManager = m::mock('Claroline\CoreBundle\Manager\UserManager');
        $this->roleManager = m::mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->workspaceTagManager = m::mock('Claroline\CoreBundle\Manager\WorkspaceTagManager');
        $this->eventDispatcher = m::mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->pagerFactory = m::mock('Claroline\CoreBundle\Pager\PagerFactory');
        $this->security = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->router = m::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
    }

    public function testRegisteredUsersListActionWithSearch()
    {
        $controller = $this->getController(array('checkRegistration'));
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $token = m::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $this->security->shouldReceive('getToken')->once()->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn('user');
        $this->security
            ->shouldReceive('isGranted')
            ->with('user_management', $workspace)
            ->once()
            ->andReturn(true);
        $this->userManager
            ->shouldReceive('getUsersByWorkspaceAndName')
            ->with($workspace, 'search', 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('workspace' => $workspace, 'pager' => 'pager', 'search' => 'search'),
            $controller->registeredUsersListAction($workspace, 1, 'search')
        );
    }

    public function testRegisteredUsersListActionWithoutSearch()
    {
        $controller = $this->getController(array('checkRegistration'));
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $token = m::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $this->security->shouldReceive('getToken')->once()->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn('user');
        $this->security
            ->shouldReceive('isGranted')
            ->with('user_management', $workspace)
            ->once()
            ->andReturn(true);
        $this->userManager
            ->shouldReceive('getUsersByWorkspace')
            ->with($workspace, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('workspace' => $workspace, 'pager' => 'pager', 'search' => ''),
            $controller->registeredUsersListAction($workspace, 1, '')
        );
    }

    private function getController(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {

            return new UserController(
                $this->userManager,
                $this->roleManager,
                $this->workspaceTagManager,
                $this->eventDispatcher,
                $this->pagerFactory,
                $this->security,
                $this->router
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return m::mock(
            'Claroline\CoreBundle\Controller\Tool\UserController' . $stringMocked,
            array(
                $this->userManager,
                $this->roleManager,
                $this->workspaceTagManager,
                $this->eventDispatcher,
                $this->pagerFactory,
                $this->security,
                $this->router
            )
        );
    }
}