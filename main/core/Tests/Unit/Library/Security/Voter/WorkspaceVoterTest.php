<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class WorkspaceVoterTest extends MockeryTestCase
{
    private $em;
    private $ut;
    private $translator;
    private $voter;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @dataProvider canDoProvider
     */
    public function testCanDo(
        $canDo,
        $action,
        $openableTools
    ) {
        $voter = $this->getVoter();
        $workspace = new Workspace();
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $repo = $this->mock('Claroline\CoreBundle\Repository\ToolRepository');
        $this->em->shouldReceive('getRepository')->with('ClarolineCoreBundle:Tool\Tool')->andReturn($repo);
        $repo->shouldReceive('findDisplayedByRolesAndWorkspace')->andReturn($openableTools);
        $this->ut->shouldReceive('getRoles')->andReturn(array());
        $this->assertEquals($canDo, $voter->canDo($workspace, $token, $action));
    }

    /**
     * @dataProvider voteProvider
     */
    public function testVote(
        $attributes,
        $result,
        $roles,
        $workspaceRegistered,
        $canDo
    ) {
        $voter = $this->getVoter(array('canDo'));
        $workspace = new Workspace();
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $manager = $this->mock('Claroline\CoreBundle\Entity\Role');
        $manager->shouldReceive('getName')->andReturn('ROLE_WS_MANAGER');
        $roleRepo = $this->mock('Claroline\CoreBundle\Repository\RoleRepository');
        $workspaceRepo = $this->mock('Claroline\CoreBundle\Repository\WorkspaceRepository');
        $this->em->shouldReceive('getRepository')->with('ClarolineCoreBundle:Workspace\Workspace')
            ->andReturn($workspaceRepo);
        $this->em->shouldReceive('getRepository')->with('ClarolineCoreBundle:Role')->andReturn($roleRepo);
        $roleRepo->shouldReceive('findManagerRole')->andReturn($manager);
        $this->ut->shouldReceive('getRoles')->andReturn($roles);
        $workspaceRepo->shouldReceive('findWorkspaceByWorkspaceAndRoles')->andReturn($workspaceRegistered);
        $voter->shouldReceive('canDo')->andReturn($canDo);
        $this->assertEquals($result, $voter->vote($token, $workspace, $attributes));
    }

    public function voteProvider()
    {
        $ws = new Workspace();

        return array(
            //manager
            array(
                'attributes' => array(),
                'result' => 1,
                'roles' => array('ROLE_WS_MANAGER'),
                'workspaceRegistered' => null,
                'canDo' => null,
            ),
            //isGranted($workspace) is valid
            array(
                'attributes' => array(),
                'result' => 1,
                'roles' => array('ROLE_WS_COLLABORATOR'),
                'workspaceRegistered' => $ws,
                'canDo' => null,
            ),
            //isGranted($workspace) is invalid
            array(
                'attributes' => array(),
                'result' => -1,
                'roles' => array('ROLE_WS_COLLABORATOR'),
                'workspaceRegistered' => null,
                'canDo' => null,
            ),
            //isGranted($workspace, 'home')
            array(
                'attributes' => array('home'),
                'result' => 1,
                'roles' => array('ROLE_WS_COLLABORATOR'),
                'workspaceRegistered' => null,
                'canDo' => true,
            ),
            //isGranted($workspace, 'home') not valid
            array(
                'attributes' => array('home'),
                'result' => -1,
                'roles' => array('ROLE_WS_COLLABORATOR'),
                'workspaceRegistered' => null,
                'canDo' => false,
            ),
        );
    }

    public function canDoProvider()
    {
        $tool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $tool->shouldReceive('getName')->andReturn('home');

        return array(
            //valid
            array(
                'canDo' => true,
                'action' => 'open',
                'openableTools' => array($tool),
            ),
            //valid tool
            array(
                'canDo' => true,
                'action' => 'home',
                'openableTools' => array($tool),
            ),
            //invalid tool
            array(
                'canDo' => false,
                'action' => 'invalid',
                'openableTools' => array($tool),
            ),
        );
    }

    private function getVoter(array $mockedMethods = array())
    {
        $this->em = $this->mock("Doctrine\ORM\EntityManager");
        $this->ut = $this->mock("Claroline\CoreBundle\Library\Security\Utilities");
        $this->translator = $this->mock("Symfony\Component\Translation\Translator");

        if (count($mockedMethods) === 0) {
            return new WorkspaceVoter($this->em, $this->translator, $this->ut);
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Library\Security\Voter\WorkspaceVoter'.$stringMocked,
            array($this->em, $this->translator, $this->ut)
        );
    }
}
