<?php

namespace Claroline\CoreBundle\Controller\Tool;

use \Mockery as m;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class WorkspaceCalendarControllerTest extends MockeryTestCase
{
    private $security;
    private $formFactory;
    private $om;
    private $request;
    private $controller;

    protected function setUp()
    {
        parent::setUp();
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->formFactory = $this->mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->security = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');

    }

    public function testAddEventAction()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $date = $this->mock('DateTime');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $this->security
            ->shouldReceive('isGranted')
            ->with('agenda', $workspace)
            ->once()
            ->andReturn(true);
        $this->security->shouldReceive('getToken')->once()->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $event = $this->mock('Claroline\CoreBundle\Entity\Event');
        $form = $this->mock('Symfony\Component\Form\Form');
        $this->formFactory->shouldReceive('create')
            ->once()
            ->with(FormFactory::TYPE_AGENDA)
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->once()
            ->with($this->request);
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $form->shouldReceive('getData')
            ->once()
            ->andReturn($event);
        $event->shouldReceive('setWorkspace')->once()->with($workspace);
        $event->shouldReceive('setUser')->once()->with($user);
        $this->om->shouldReceive('persist')->once()->with($event);
        $this->om->shouldReceive('flush')->once();
        $event->shouldReceive('getId')->once()->andReturn('1');
        $event->shouldReceive('getTitle')->once()->andReturn('title');
        $event->shouldReceive('getStart')->once()->andReturn($date);
        $date->shouldReceive('getTimestamp')->once()->andReturn('123456');
        $event->shouldReceive('getEnd')->once()->andReturn($date);
        $date->shouldReceive('getTimestamp')->once()->andReturn('123457');
        $event->shouldReceive('getPriority')->once()->andReturn('#BBBDDD');
        $event->shouldReceive('getAllDay')->once()->andReturn(false);
        $event->shouldReceive('getDescription')->once()->andReturn('blabla');
        $this->assertEquals(
            new Response(
                json_encode(
                    array('id' => '1'
                        ,'title' => 'title',
                        'start' => '123456',
                        'end' => '123457',
                        'color' => '#BBBDDD',
                        'allDay' => false,
                        'description' => 'blabla'
                    )
                ),
                200,
                array('Content-Type' => 'application/json')
            ),
            $this->getController(array('checkUserIsAllowed'))->addEventAction($workspace)
        );
    }

     public function testUpdateAction()
     {

        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $event = $this->mock('Claroline\CoreBundle\Entity\Event');
        $eventRepo = $this->mock('Claroline\CoreBundle\Repository\EventRepository');
        $date = $this->mock('DateTime');
        $parameterBag = $this->mock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->security
            ->shouldReceive('isGranted')
            ->with('agenda', $workspace)
            ->once()
            ->andReturn(true);
        $this->security->shouldReceive('getToken')->once()->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $this->request->request = $parameterBag;
        $parameterBag->shouldReceive('all')->once()->andReturn(array('id' => '8'));
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Event')->andReturn($eventRepo);
        $eventRepo->shouldReceive('find')->with('8')->andReturn($event);
        $form = $this->mock('Symfony\Component\Form\Form');
        $this->formFactory->shouldReceive('create')
             ->once()
             ->with(FormFactory::TYPE_AGENDA, array(), $event)
             ->andReturn($form);
             $form->shouldReceive('handleRequest')
                 ->once()
                 ->with($this->request);
             $form->shouldReceive('isValid')
                 ->once()
                 ->andReturn(true);
        $this->om->shouldReceive('persist')->once()->with($event);
        $this->om->shouldReceive('flush')->once();
        $event->shouldReceive('getId')->once()->andReturn('1');
        $event->shouldReceive('getTitle')->once()->andReturn('title');
        $event->shouldReceive('getStart')->once()->andReturn($date);
        $date->shouldReceive('getTimestamp')->once()->andReturn('123456');
        $event->shouldReceive('getEnd')->once()->andReturn($date);
        $date->shouldReceive('getTimestamp')->once()->andReturn('123457');
        $event->shouldReceive('getPriority')->once()->andReturn('#BBBDDD');
        $event->shouldReceive('getAllDay')->once()->andReturn(false);
        $event->shouldReceive('getDescription')->once()->andReturn('blabla');
        $this->assertEquals(
            new Response(
                json_encode(
                    array('id' => '1'
                        ,'title' => 'title',
                        'start' => '123456',
                        'end' => '123457',
                        'color' => '#BBBDDD',
                        'allDay' => false,
                        'description' => 'blabla'
                    )
                ),
                200,
                array('Content-Type' => 'application/json')
            ),
            $this->getController(array('checkUserIsAllowed'))->updateAction($workspace)
        );
     }

    private function getController (array $mockedMethods = array())
    {

        if (count($mockedMethods) === 0) {

            return new WorkspaceAgendaController(
                $this->security,
                $this->formFactory,
                $this->om,
                $this->request
            );
        } else {
            $stringMocked = '[';
                $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return $this->mock(
                'Claroline\CoreBundle\Controller\Tool\WorkspaceAgendaController' . $stringMocked,
                array(
                    $this->security,
                    $this->formFactory,
                    $this->om,
                    $this->request
                )
            );
        }
    }
}