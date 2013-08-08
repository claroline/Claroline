<?php

namespace Claroline\CoreBundle\Controller;

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
        $this->markTestSkipped('Unable to test event entity');
        $workspace = new SimpleWorkspace();
         $event = m::on(function ($event) {
            return $event->getId() === '1'
                && $event->getTitle() === 'hello'
                && $event->getStart()->getTimestamp() === 123456789
                && $event->getEnd()->getTimestamp() === 123456789
                && $event->getPriority() === '#000'
                && $event->getAllDay() === true;
            }
        );
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $controller = $this->getController(array('checkUserIsAllowed'));
        $this->security
            ->shouldReceive('isGranted')
            ->with('calendar', $workspace)
            ->once()
            ->andReturn(true);
        $form = $this->mock('Symfony\Component\Form\Form');
        $this->formFactory->shouldReceive('create')
            ->once()
            ->with(FormFactory::TYPE_CALENDAR, array(), anInstanceOf('Claroline\CoreBundle\Entity\Event'))
            ->andReturn($form);
            $form->shouldReceive('handleRequest')
                ->once()
                ->with($this->request);
            $form->shouldReceive('isValid')
                ->once()
                ->andReturn(true);
        $event->shouldReceive('setWorkspace')->once()->with($workspace);
        $this->security->shouldReceive('getToken')->once()->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $event->shouldReceive('setUser')->once()->with($user);
        $this->om->shouldReceive('persist')->once()->with($event);
        $this->om->shouldReceive('flush')->once();
        $event->shouldReceive();
        $this->assertEquals(new Response(json_encode(""),200,array('Content-Type' => 'application/json')), $controller->addEventAction($workspace));
    }
    
    public function testUpdateAction()
    {
        $user = new User();
        $controller = $this->getController(array('checkUserIsAllowed'));
        $this->security
            ->shouldReceive('isGranted')
            ->with('calendar', $workspace)
            ->once()
            ->andReturn(true);
    }

    private function getController (array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
                
           return new Tool\WorkspaceCalendarController(
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
                'Claroline\CoreBundle\Controller\Tool\WorkspaceCalendarController' . $stringMocked,
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