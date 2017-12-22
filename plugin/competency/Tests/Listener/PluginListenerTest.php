<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use HeVinci\CompetencyBundle\Util\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class PluginListenerTest extends UnitTestCase
{
    private $request;
    private $response;
    private $listener;

    protected function setUp()
    {
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $kernel = $this->mock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $stack = new RequestStack();
        $stack->push($this->request);
        $this->listener = new PluginListener($stack, $kernel);
        $this->response = new Response('Test response');
        $kernel->expects($this->once())->method('handle')->willReturn($this->response);
    }

    public function testOpenCompetencyTool()
    {
        $this->expectSubRequest(['_controller' => 'HeVinciCompetencyBundle:Competency:frameworks']);
        $event = new OpenAdministrationToolEvent('competencies');
        $this->listener->onOpenCompetencyTool($event);
        $this->assertEquals($this->response, $event->getResponse());
    }

    public function testOpenLearningObjectivesTool()
    {
        $this->expectSubRequest(['_controller' => 'HeVinciCompetencyBundle:Objective:objectives']);
        $event = new OpenAdministrationToolEvent('learning-objectives');
        $this->listener->onOpenLearningObjectivesTool($event);
        $this->assertEquals($this->response, $event->getResponse());
    }

    public function testOpenMyLearningObjectivesTool()
    {
        $this->expectSubRequest(['_controller' => 'HeVinciCompetencyBundle:MyObjective:objectives']);
        $event = new DisplayToolEvent();
        $this->listener->onOpenMyLearningObjectivesTool($event);
        $this->assertEquals('Test response', $event->getContent());
    }

    public function testOpenResourceCompetencies()
    {
        $this->markTestSkipped('Cannot access to resource node in mock resource');
        $this->expectSubRequest(['_controller' => 'HeVinciCompetencyBundle:Resource:competencies', 'id' => 14]);
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\Text');
        $resource->expects($this->once())->method('getId')->willReturn(14);
        $event = new CustomActionResourceEvent($resource);
        $this->listener->onOpenResourceCompetencies($event);
        $this->assertEquals($this->response, $event->getResponse());
    }

    public function testDisplayObjectivesWidget()
    {
        $this->expectSubRequest(['_controller' => 'HeVinciCompetencyBundle:Widget:objectives']);
        $event = new DisplayWidgetEvent($this->mock('Claroline\CoreBundle\Entity\Widget\WidgetInstance'));
        $this->listener->onDisplayObjectivesWidget($event);
        $this->assertEquals('Test response', $event->getContent());
    }

    private function expectSubRequest(array $attributes)
    {
        $this->request
            ->expects($this->once())
            ->method('duplicate')
            ->with([], null, $attributes)
            ->willReturn(new Request());
    }
}
