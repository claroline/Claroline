<?php

namespace Claroline\CoreBundle\Controller;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Controller\HomeController;

class HomeControllerTest extends MockeryTestCase
{
    private $request;
    private $manager;
    private $response;
    private $type;
    private $region;
    private $content;
    private $controller;


    public function setUp()
    {
        parent::setUp();

        $this->request = m::mock('Symfony\Component\HttpFoundation\Request');
        $this->manager = m::mock('Claroline\CoreBundle\Manager\HomeManager');
        $this->response = m::mock('Symfony\Component\HttpFoundation\Response');

        $this->type = m::mock('Claroline\CoreBundle\Entity\Home\Type');
        $this->region = m::mock('Claroline\CoreBundle\Entity\Home\Region');
        $this->content = m::mock('Claroline\CoreBundle\Entity\Home\Content');

        $this->controller = new HomeController($this->manager, $this->request);
    }

    //@TODO test with father not null
    public function testcontentAction()
    {
        $this->type->shouldReceive('getName')->once();
        $this->manager->shouldReceive('getContent')->once();
        $this->manager->shouldReceive('render')->once()->andReturn($this->response);
        $this->assertEquals($this->response, $this->controller->contentAction($this->content, $this->type, null));
    }

    public function testhomeAction()
    {
        $this->response->shouldReceive('getContent')->once();
        $this->manager->shouldReceive('getRegions')->once();
        $this->manager->shouldReceive('contentLayout')->once()->andReturn($this->response);
        $this->assertEquals(array("region" => null, "content" => null), $this->controller->homeAction($this->type));
    }

    public function testtypeAction()
    {
        $this->manager->shouldReceive('contentLayout')->once()->andReturn($this->response);
        $this->assertEquals($this->response, $this->controller->typeAction($this->type, $this->content, $this->region));
    }

    public function testtypesAction()
    {
        $this->response->shouldReceive('getContent')->once();
        $this->manager->shouldReceive('getTypes')->once();
        $this->manager->shouldReceive('getRegions')->once();
        $this->manager->shouldReceive('render')->once()->andReturn($this->response);
        $this->assertEquals(array("region" => null, "content" => null), $this->controller->typesAction());
    }

    //@TODO test with values not null
    public function testcreatorAction()
    {
        $this->manager->shouldReceive('getCreator')->once()->andReturn($this->response);
        $this->assertEquals($this->response, $this->controller->creatorAction("home", 1, null, null));
    }

    public function testsizeAction()
    {
        $this->assertEquals(
            array("id" => 1, "size" => "span12", "type" => "home"),
            $this->controller->sizeAction(1, 'span12', 'home')
        );
    }

    public function testgraphAction()
    {
        $this->request->shouldReceive('get')->once();
        $this->manager->shouldReceive('getGraph')->once()->andReturn($this->response);
        $this->assertEquals($this->response, $this->controller->graphAction());
    }

    public function testregionAction()
    {
        $this->assertEquals(array("id" => 1), $this->controller->regionAction(1));
    }

    public function testcreateAction()
    {
        $this->request->shouldReceive('get')->times(5);
        $this->manager->shouldReceive('createContent')->once()->andReturn("true");
        $this->assertEquals("true", $this->controller->createAction());
    }

    public function testupdateAction()
    {
        $this->request->shouldReceive('get')->times(5);
        $this->manager->shouldReceive('updateContent')->once()->andReturn("true");
        $this->assertEquals("true", $this->controller->updateAction($this->content));
    }

    public function testreorderAction()
    {
        $this->manager->shouldReceive('reorderContent')->once()->andReturn("true");
        $this->assertEquals("true", $this->controller->reorderAction($this->type, $this->content, $this->content));
    }

    public function testdeleteAction()
    {
        $this->manager->shouldReceive('deleteContent')->once()->andReturn("true");
        $this->assertEquals("true", $this->controller->deleteAction($this->content));
    }

    public function testcontentToRegionAction()
    {
        $this->manager->shouldReceive('contentToRegion')->once()->andReturn("true");
        $this->assertEquals("true", $this->controller->contentToRegionAction($this->region, $this->content));
    }
}


