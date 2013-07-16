<?php

namespace Claroline\CoreBundle\Controller;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Controller\HomeController;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends MockeryTestCase
{
    private $type;
    private $region;
    private $content;

    private $manager;
    private $request;
    private $security;
    //private $response;
    private $templating;
    private $homeService;

    private $controller;

    public function setUp()
    {
        parent::setUp();

        $this->type = m::mock('Claroline\CoreBundle\Entity\Home\Type');
        $this->region = m::mock('Claroline\CoreBundle\Entity\Home\Region');
        $this->content = m::mock('Claroline\CoreBundle\Entity\Home\Content');

        $this->manager = m::mock('Claroline\CoreBundle\Manager\HomeManager');
        $this->request = m::mock('Symfony\Component\HttpFoundation\Request');
        $this->security = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        //$this->response = m::mock('Symfony\Component\HttpFoundation\ResponseInterface');
        $this->templating = m::mock('Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine');
        $this->homeService = m::mock('Claroline\CoreBundle\Library\Home\HomeService');

        $this->controller = new HomeController(
            $this->manager, $this->request, $this->security, $this->templating, $this->homeService
        );
    }

    //@TODO test with father not null
    public function testcontentAction()
    {
        $this->type->shouldReceive('getName')->once();
        $this->manager->shouldReceive('getContent')->once()->andReturn(array());
        $this->homeService->shouldReceive('defaultTemplate')->once();
        $this->templating->shouldReceive('render')->once();
        $this->assertEquals(new Response, $this->controller->contentAction($this->content, $this->type, null));
    }

    public function testhomeAction()
    {
        $this->manager->shouldReceive('getRegionContents')->once()->andReturn(
            array('header' => array(array('type' => 'home')))
        );
        $this->manager->shouldReceive('contentLayout')->once()->andReturn(array('content' => array('type' => 'home')));
        $this->security->shouldReceive('isGranted')->with('ROLE_ADMIN')->once()->andReturn(true);
        $this->homeService->shouldReceive('defaultTemplate')->once();
        $this->templating->shouldReceive('render')->times(2);
        $this->assertEquals(
            array('region' => array('header' => ''), 'content' => ''),
            $this->controller->homeAction($this->type)
        );
    }

    public function testtypeAction()
    {
        $this->manager->shouldReceive('contentLayout')->once()->andReturn(array('content' => array('type' => 'home')));
        $this->templating->shouldReceive('render')->once();
        $this->assertEquals(new Response, $this->controller->typeAction($this->type, $this->content, $this->region));
    }

    public function testtypesAction()
    {
        $this->manager->shouldReceive('getTypes')->once();
        $this->manager->shouldReceive('getRegionContents')->once()->andReturn(
            array('header' => array(array('type' => 'home')))
        );
        $this->security->shouldReceive('isGranted')->with('ROLE_ADMIN')->once()->andReturn(true);
        $this->homeService->shouldReceive('defaultTemplate')->once();
        $this->templating->shouldReceive('render')->times(2);
        $this->assertEquals(
            array('region' => array('header' => ''), 'content' => ''),
            $this->controller->typesAction()
        );
    }

    //@TODO test with values not null
    public function testcreatorAction()
    {
        $this->manager->shouldReceive('getCreator')->once()->andReturn(array());
        $this->security->shouldReceive('isGranted')->with('ROLE_ADMIN')->once()->andReturn(true);
        $this->homeService->shouldReceive('defaultTemplate')->once();
        $this->templating->shouldReceive('render')->once();
        $this->assertEquals(new Response, $this->controller->creatorAction('home', 1, null, null));
    }

    public function testsizeAction()
    {
        $this->assertEquals(
            array('id' => 1, 'size' => 'span12', 'type' => 'home'),
            $this->controller->sizeAction(1, 'span12', 'home')
        );
    }

    public function testgraphAction()
    {
        $this->request->shouldReceive('get')->once();
        $this->manager->shouldReceive('getGraph')->once()->andReturn(array('type' => 'video'));
        $this->homeService->shouldReceive('defaultTemplate')->once();
        $this->templating->shouldReceive('render')->once();
        $this->assertEquals(new Response, $this->controller->graphAction());
    }

    public function testregionAction()
    {
        $this->assertEquals(array('id' => 1), $this->controller->regionAction(1));
    }

    public function testcreateAction()
    {
        $this->request->shouldReceive('get')->times(5);
        $this->manager->shouldReceive('createContent')->once()->andReturn('true');
        $this->assertEquals(new Response('true'), $this->controller->createAction());
    }

    public function testupdateAction()
    {
        $this->request->shouldReceive('get')->times(5);
        $this->manager->shouldReceive('updateContent')->once()->andReturn('true');
        $this->assertEquals(new Response('true'), $this->controller->updateAction($this->content));
    }

    public function testreorderAction()
    {
        $this->manager->shouldReceive('reorderContent')->once()->andReturn('true');
        $this->assertEquals(
            new Response('true'),
            $this->controller->reorderAction($this->type, $this->content, $this->content)
        );
    }

    public function testdeleteAction()
    {
        $this->manager->shouldReceive('deleteContent')->once()->andReturn('true');
        $this->assertEquals(new Response('true'), $this->controller->deleteAction($this->content));
    }

    public function testcontentToRegionAction()
    {
        $this->manager->shouldReceive('contentToRegion')->once()->andReturn('true');
        $this->assertEquals(
            new Response('true'),
            $this->controller->contentToRegionAction($this->region, $this->content)
        );
    }
}


