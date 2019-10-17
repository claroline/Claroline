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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class WorkspaceVoterTest extends MockeryTestCase
{
    private $em;
    private $ut;
    private $translator;
    private $voter;

    public function setUp(): void
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
        $repo = $this->mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
        $this->em->shouldReceive('getRepository')->with('ClarolineCoreBundle:Tool\Tool')->andReturn($repo);
        $repo->shouldReceive('findDisplayedByRolesAndWorkspace')->andReturn($openableTools);
        $this->ut->shouldReceive('getRoles')->andReturn([]);
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
        $voter = $this->getVoter(['canDo']);
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

        return [
            //manager
            [
                'attributes' => [],
                'result' => 1,
                'roles' => ['ROLE_WS_MANAGER'],
                'workspaceRegistered' => null,
                'canDo' => null,
            ],
            //isGranted($workspace) is valid
            [
                'attributes' => [],
                'result' => 1,
                'roles' => ['ROLE_WS_COLLABORATOR'],
                'workspaceRegistered' => $ws,
                'canDo' => null,
            ],
            //isGranted($workspace) is invalid
            [
                'attributes' => [],
                'result' => -1,
                'roles' => ['ROLE_WS_COLLABORATOR'],
                'workspaceRegistered' => null,
                'canDo' => null,
            ],
            //isGranted($workspace, 'home')
            [
                'attributes' => ['home'],
                'result' => 1,
                'roles' => ['ROLE_WS_COLLABORATOR'],
                'workspaceRegistered' => null,
                'canDo' => true,
            ],
            //isGranted($workspace, 'home') not valid
            [
                'attributes' => ['home'],
                'result' => -1,
                'roles' => ['ROLE_WS_COLLABORATOR'],
                'workspaceRegistered' => null,
                'canDo' => false,
            ],
        ];
    }

    public function canDoProvider()
    {
        $tool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $tool->shouldReceive('getName')->andReturn('home');

        return [
            //valid
            [
                'canDo' => true,
                'action' => 'open',
                'openableTools' => [$tool],
            ],
            //valid tool
            [
                'canDo' => true,
                'action' => 'home',
                'openableTools' => [$tool],
            ],
            //invalid tool
            [
                'canDo' => false,
                'action' => 'invalid',
                'openableTools' => [$tool],
            ],
        ];
    }

    private function getVoter(array $mockedMethods = [])
    {
        $this->em = $this->mock("Doctrine\ORM\EntityManager");
        $this->ut = $this->mock("Claroline\CoreBundle\Library\Security\Utilities");
        $this->translator = $this->mock("Symfony\Component\Translation\Translator");

        if (0 === count($mockedMethods)) {
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
            [$this->em, $this->translator, $this->ut]
        );
    }
}
