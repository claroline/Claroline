<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Role;

class RoleManagerTest extends MockeryTestCase
{
    private $writer;

    public function setUp()
    {
        parent::setUp();

        $this->writer = m::mock('Claroline\CoreBundle\Writer\RoleWriter');
    }

    public function testInitBaseWorkspaceRole()
    {
        $ws = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $roleUser = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleSuperUser = m::mock('Claroline\CoreBundle\Entity\Role');

        $params = array(
            'ROLE_WS_USER' => 'user',
            'ROLE_WS_SUPERUSER' => 'superuser'
        );

        $ws->shouldReceive('getId')->times(2)->andReturn(1);

        $this->writer
            ->shouldReceive('create')
            ->once()
            ->with('ROLE_WS_USER_1', 'user', Role::WS_ROLE, $ws)
            ->andReturn($roleUser);
        $this->writer
            ->shouldReceive('create')
            ->once()
            ->with('ROLE_WS_SUPERUSER_1', 'superuser', Role::WS_ROLE, $ws)
            ->andReturn($roleSuperUser);

        $result = $this->getManager()->initWorkspaceBaseRole($params, $ws);
        $expectedResult = array(
            'ROLE_WS_USER' => $roleUser,
            'ROLE_WS_SUPERUSER' => $roleSuperUser
        );

        $this->assertEquals($result, $expectedResult);
    }

    private function getManager(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
            return new RoleManager($this->writer);
        } else {
            $stringMocked = '[';
                $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return m::mock('Claroline\CoreBundle\Manager\RoleManager' . $stringMocked, array($this->writer));
        }
    }
}