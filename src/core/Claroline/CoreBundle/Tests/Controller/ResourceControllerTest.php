<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

/**
 * Controller for user self-registration. Access to this functionality requires
 * that the user is anonymous and the self-registration is allowed by the
 * platform configuration.
 */
class ResourceControllerTest extends MockeryTestCase
{
    private $sc;
    private $resourceManager;
    private $rightsManager;
    private $roleManager;
    private $translator;
    private $request;
    private $dispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->sc = $this->mock('Symfony\Component\Security\Core\SecurityContext');
        $this->resourceManager = $this->mock('Claroline\CoreBundle\Manager\ResourceManager');
        $this->rightsManager = $this->mock('Claroline\CoreBundle\Manager\RightsManager');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->translator = $this->mock('Symfony\Component\Translation\Translator');
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->dispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
    }

    public function testCustomAction()
    {
        $controller = $this->getController(array('checkAccess'));
        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $res = $this->mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $rt = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $customActionEvent = $this->mock('Claroline\CoreBundle\Event\Event\CustomActionResourceEvent');
        $node->shouldReceive('getResourceType')->once()->andReturn($rt);
        $rt->shouldReceive('getName')->andReturn('resourcetype');
        $controller->shouldReceive('checkAccess')
            ->with('OPEN', anInstanceOf('Claroline\CoreBundle\Library\Resource\ResourceCollection'));
        $this->resourceManager->shouldReceive('getResourceFromNode')->with($node)->once()->andReturn($res);

        $this->dispatcher->shouldReceive('dispatch')->once()
            ->with('action_resourcetype', 'CustomActionResource', array($res))
            ->andReturn($customActionEvent);

        $response = new \Symfony\Component\HttpFoundation\Response;
        $customActionEvent->shouldReceive('getResponse')->andReturn($response);
        $this->assertEquals($response->getContent(), $controller->customAction('action', $node)->getContent());
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $controller->customAction('action', $node)
        );
    }

    private function getController(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
            return new ResourceController(
                $this->sc,
                $this->resourceManager,
                $this->rightsManager,
                $this->roleManager,
                $this->translator,
                $this->request,
                $this->dispatcher
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Controller\ResourceController' . $stringMocked,
            array(
                $this->sc,
                $this->resourceManager,
                $this->rightsManager,
                $this->roleManager,
                $this->translator,
                $this->request,
                $this->dispatcher
            )
        );
    }
}
