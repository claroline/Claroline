<?php

namespace Claroline\ActivityToolBundle\Listener;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class ToolListenerTest extends MockeryTestCase
{
    private $container;
    private $toolListener;

    protected function setUp()
    {
        parent::setUp();
        $this->container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->toolListener = new ToolListener();
        $this->toolListener->setContainer($this->container);
    }

    public function testFetchActivitiesDatasForDesktopTool()
    {
        $em = m::mock('Doctrine\ORM\EntityManager');
        $sc = m::mock('Symfony\Component\Security\Core\SecurityContext');
        $ut = m::mock('Claroline\CoreBundle\Library\Security\Utilities');
        $token = m::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $activityRepo = m::mock('Claroline\CoreBundle\Repository\ActivityRepository');
        $resourceRepo = m::mock('Claroline\CoreBundle\Repository\AbstractResourceRepository');
        $startDate = m::mock('\DateTime');

        $this->container->shouldReceive('get')
            ->with('doctrine.orm.entity_manager')
            ->once()
            ->andReturn($em);
        $this->container->shouldReceive('get')
            ->with('security.context')
            ->once()
            ->andReturn($sc);
        $this->container->shouldReceive('get')
            ->with('claroline.security.utilities')
            ->once()
            ->andReturn($ut);
        $sc->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $ut->shouldReceive('getRoles')
            ->with($token)
            ->once()
            ->andReturn(array());
        $em->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Resource\AbstractResource')
            ->times(2)
            ->andReturn($resourceRepo);
        $em->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Resource\Activity')
            ->once()
            ->andReturn($activityRepo);
        $resourceRepo->shouldReceive('findByCriteria')
            ->with(array('roots' => array(), 'types' => array('activity')), array(), true)
            ->once()
            ->andReturn(
                array(
                    array(
                        'id' => 1,
                        'name' => 'my_resource',
                        'path' => 'my_path',
                        'parent_id' => 2,
                        'creator_username' => 'my_name',
                        'type' => 'activity',
                        'is_browsable' => true,
                        'previous_id' => 3,
                        'next_id' => 4,
                        'large_icon' => 'no_icon'
                    )
                )
            );
        $resourceRepo->shouldReceive('findWorkspaceInfoByIds')
            ->with(array(1))
            ->once()
            ->andReturn(
                array(
                    array(
                        'id' => 1,
                        'code' => 'workspace_code',
                        'name' => 'workspace_name'
                    )
                )
            );
        $activityRepo->shouldReceive('findActivitiesByIds')
            ->with(array(1))
            ->once()
            ->andReturn(
                array(
                    array(
                        'id' => 1,
                        'instructions' => 'my_instructions',
                        'startDate' => $startDate,
                        'endDate' => null
                    )
                )
            );
        $startDate->shouldReceive('format')
            ->with('Y-m-d H:i:s')
            ->once()
            ->andReturn('2013-06-18 10:49:00');

        $this->assertEquals(
            array(
                'resourceInfos' => array(
                    1 => array(
                        'id' => 1,
                        'name' => 'my_resource',
                        'path' => 'my_path',
                        'parent_id' => 2,
                        'creator_username' => 'my_name',
                        'type' => 'activity',
                        'is_browsable' => true,
                        'previous_id' => 3,
                        'next_id' => 4,
                        'large_icon' => 'no_icon'
                    )
                ),
                'activityInfos' => array(
                    1 => array(
                        'instructions' => 'my_instructions',
                        'startDate' => '2013-06-18 10:49:00',
                        'endDate' => '-'
                    )
                ),
                'workspaceInfos' => array(
                    'workspace_code' => array(
                        'code' => 'workspace_code',
                        'name' => 'workspace_name',
                        'resources' => array(1)
                    )
                )
            ),
            $this->toolListener->fetchActivitiesDatas(true, null)
        );
    }

    public function testFetchActivitiesDatasForWorkspaceTool()
    {
        $em = m::mock('Doctrine\ORM\EntityManager');
        $sc = m::mock('Symfony\Component\Security\Core\SecurityContext');
        $ut = m::mock('Claroline\CoreBundle\Library\Security\Utilities');
        $token = m::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $activityRepo = m::mock('Claroline\CoreBundle\Repository\ActivityRepository');
        $resourceRepo = m::mock('Claroline\CoreBundle\Repository\AbstractResourceRepository');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $root = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $startDate = m::mock('\DateTime');

        $this->container->shouldReceive('get')
            ->with('doctrine.orm.entity_manager')
            ->once()
            ->andReturn($em);
        $this->container->shouldReceive('get')
            ->with('security.context')
            ->once()
            ->andReturn($sc);
        $this->container->shouldReceive('get')
            ->with('claroline.security.utilities')
            ->once()
            ->andReturn($ut);
        $sc->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $ut->shouldReceive('getRoles')
            ->with($token)
            ->once()
            ->andReturn(array());
        $em->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Resource\AbstractResource')
            ->times(2)
            ->andReturn($resourceRepo);
        $em->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Resource\Activity')
            ->once()
            ->andReturn($activityRepo);
        $resourceRepo->shouldReceive('findWorkspaceRoot')
            ->with($workspace)
            ->once()
            ->andReturn($root);
        $root->shouldReceive('getPath')
            ->once()
            ->andReturn('my_path');
        $resourceRepo->shouldReceive('findByCriteria')
            ->with(array('roots' => array('my_path'), 'types' => array('activity')), array(), true)
            ->once()
            ->andReturn(
                array(
                    array(
                        'id' => 1,
                        'name' => 'my_resource',
                        'path' => 'my_path',
                        'parent_id' => 2,
                        'creator_username' => 'my_name',
                        'type' => 'activity',
                        'is_browsable' => true,
                        'previous_id' => 3,
                        'next_id' => 4,
                        'large_icon' => 'no_icon'
                    )
                )
            );
        $activityRepo->shouldReceive('findActivitiesByIds')
            ->with(array(1))
            ->once()
            ->andReturn(
                array(
                    array(
                        'id' => 1,
                        'instructions' => 'my_instructions',
                        'startDate' => $startDate,
                        'endDate' => null
                    )
                )
            );
        $startDate->shouldReceive('format')
            ->with('Y-m-d H:i:s')
            ->once()
            ->andReturn('2013-06-18 10:49:00');

        $this->assertEquals(
            array(
                'resourceInfos' => array(
                    1 => array(
                        'id' => 1,
                        'name' => 'my_resource',
                        'path' => 'my_path',
                        'parent_id' => 2,
                        'creator_username' => 'my_name',
                        'type' => 'activity',
                        'is_browsable' => true,
                        'previous_id' => 3,
                        'next_id' => 4,
                        'large_icon' => 'no_icon'
                    )
                ),
                'activityInfos' => array(
                    1 => array(
                        'instructions' => 'my_instructions',
                        'startDate' => '2013-06-18 10:49:00',
                        'endDate' => '-'
                    )
                )
            ),
            $this->toolListener->fetchActivitiesDatas(false, $workspace)
        );
    }
}