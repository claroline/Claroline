<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class ResourceControllerTest extends UnitTestCase
{
    private $resourceManager;
    private $controller;

    protected function setUp()
    {
        $this->resourceManager = $this->mock('HeVinci\CompetencyBundle\Manager\ResourceManager');
        $this->controller = new ResourceController($this->resourceManager);
    }

    public function testCompetenciesAction()
    {
        $resource = new ResourceNode();
        $this->resourceManager->expects($this->once())
            ->method('loadLinkedCompetencies')
            ->with($resource)
            ->willReturn('COMPETENCIES');
        $this->assertEquals(
            [
                '_resource' => $resource,
                'resourceNode' => $resource,
                'workspace' => $resource->getWorkspace(),
                'competencies' => 'COMPETENCIES',
            ],
            $this->controller->competenciesAction($resource)
        );
    }

    public function testLinkAbilityActionForAlreadyLinkedAbilities()
    {
        $resource = new ResourceNode();
        $ability = new Ability();
        $this->resourceManager->expects($this->once())
            ->method('createLink')
            ->with($resource, $ability)
            ->willReturn(false);
        $response = $this->controller->linkAbilityAction($resource, $ability);
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * @dataProvider linkActionProvider
     *
     * @param string $method
     * @param string $target
     */
    public function testLinkActionsForAlreadyLinkedTargets($method, $target)
    {
        $resource = new ResourceNode();
        $this->resourceManager->expects($this->once())
            ->method('createLink')
            ->with($resource, $target)
            ->willReturn(false);
        $response = $this->controller->{$method}($resource, $target);
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * @dataProvider linkActionProvider
     *
     * @param string $method
     * @param string $target
     */
    public function testLinkActions($method, $target)
    {
        $resource = new ResourceNode();
        $this->resourceManager->expects($this->once())
            ->method('createLink')
            ->with($resource, $target)
            ->willReturn('TARGET');
        $response = $this->controller->{$method}($resource, $target);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(json_encode('TARGET'), $response->getContent());
    }

    /**
     * @dataProvider removeLinkActionProvider
     *
     * @param string $method
     * @param string $target
     */
    public function testRemoveLinkActions($method, $target)
    {
        $resource = new ResourceNode();
        $this->resourceManager->expects($this->once())
            ->method('removeLink')
            ->with($resource, $target)
            ->willReturn('TARGET');
        $this->assertEquals(
            json_encode('TARGET'),
            $this->controller->{$method}($resource, $target)->getContent()
        );
    }

    public function testCompetencyResourcesAction()
    {
        $competency = new Competency();
        $this->assertEquals(
            ['competency' => $competency],
            $this->controller->competencyResourcesAction($competency)
        );
    }

    public function testAbilityResourcesAction()
    {
        $ability = new Ability();
        $this->assertEquals(
            ['ability' => $ability],
            $this->controller->abilityResourcesAction($ability)
        );
    }

    public function linkActionProvider()
    {
        return [
            ['linkAbilityAction', new Ability()],
            ['linkCompetencyAction', new Competency()],
        ];
    }

    public function removeLinkActionProvider()
    {
        return [
            ['removeAbilityLinkAction', new Ability()],
            ['removeCompetencyLinkAction', new Competency()],
        ];
    }
}
