<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Manager\HomeManager;
use Symfony\Component\HttpFoundation\Response;

class HomeManagerTest extends MockeryTestCase
{
    private $graph;
    private $writer;
    private $manager;
    private $security;
    private $response;
    private $templating;
    private $homeService;

    private $type;
    private $region;
    private $content;
    private $subContent;
    private $contentType;
    private $contentRegion;
    private $repository;

    private $homeManager;

    public function setUp()
    {
        parent::setUp();

        $this->graph = m::mock('Claroline\CoreBundle\Library\Home\GraphService');
        $this->writer = m::mock('Claroline\CoreBundle\Database\Writer');
        $this->manager = m::mock('Doctrine\Bundle\DoctrineBundle\Registry');
        $this->security = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->response = m::mock('Symfony\Component\HttpFoundation\Response');
        $this->templating = m::mock('Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine');
        $this->homeService = m::mock('Claroline\CoreBundle\Library\Home\HomeService');
        $this->repository = m::mock('Doctrine\ORM\EntityRepository');

        $this->type = m::mock('Claroline\CoreBundle\Entity\Home\Type');
        $this->region = m::mock('Claroline\CoreBundle\Entity\Home\Region');
        $this->content = m::mock('Claroline\CoreBundle\Entity\Home\Content');
        $this->subContent = m::mock('Claroline\CoreBundle\Entity\Home\SubContent');
        $this->contentType = m::mock('Claroline\CoreBundle\Entity\Home\Content2Type');
        $this->contentRegion = m::mock('Claroline\CoreBundle\Entity\Home\Content2Region');

        $this->manager->shouldReceive('getRepository')->times(6)->andReturn($this->repository);

        $this->homeManager = new HomeManager(
            $this->graph, $this->homeService, $this->templating, $this->manager, $this->security, $this->writer
        );
    }

    public function testrender()
    {
        $this->homeService->shouldReceive('defaultTemplate')->once();
        $this->templating->shouldReceive('render')->once();
        $this->assertEquals(new Response(), $this->homeManager->render("", array(), true));
    }

    public function testgetContent()
    {
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->contentType);
        $this->type->shouldReceive('getName')->once();
        $this->contentType->shouldReceive('getSize')->once();
        $this->content->shouldReceive('getId')->once();
        $this->homeService->shouldReceive('isDefinedPush')->once()->andReturn(array());
        $this->templating->shouldReceive('render')->once();
        $this->assertEquals(
            array('type' => null, 'size' => null, 'menu' => '', 'content' => $this->content),
            $this->homeManager->getContent($this->content, $this->type, null)
        );
    }

    public function testgetMenu()
    {
        $this->homeService->shouldReceive('isDefinedPush')->once()->andReturn(array());
        $this->templating->shouldReceive('render')->once();
        $this->assertEquals(new Response(), $this->homeManager->getMenu(1, 'span12', 'home', null));
    }

    public function testcontentLayout()
    {
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->type);
        $this->repository->shouldReceive('find')->once()->andReturn($this->content);
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->subContent);
        $this->type->shouldReceive('getMaxContentPage')->times(2)->andReturn(1);
        $this->type->shouldReceive('getName')->times(3);
        $this->subContent->shouldReceive('getContent')->times(2)->andReturn($this->content);
        $this->subContent->shouldReceive('getSize')->times(2);
        $this->subContent->shouldReceive('getNext')->once();
        $this->content->shouldReceive('getId')->once();
        $this->homeService->shouldReceive('isDefinedPush')->times(6)->andReturn(array());
        $this->security->shouldReceive('isGranted')->with('ROLE_ADMIN')->once()->andReturn(true);
        $this->homeService->shouldReceive('defaultTemplate')->times(2)->andReturn(array());
        $this->templating->shouldReceive('render')->times(4);
        $this->assertEquals(new Response(), $this->homeManager->contentLayout("home", 1, "left"));
    }

    public function testgetContentByType()
    {
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->type);
        $this->repository->shouldReceive('find')->once()->andReturn($this->content);
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->subContent);
        $this->type->shouldReceive('getMaxContentPage')->times(2)->andReturn(1);
        $this->type->shouldReceive('getName')->times(3);
        $this->subContent->shouldReceive('getContent')->times(2)->andReturn($this->content);
        $this->subContent->shouldReceive('getSize')->times(2);
        $this->subContent->shouldReceive('getNext')->once();
        $this->content->shouldReceive('getId')->once();
        $this->homeService->shouldReceive('isDefinedPush')->times(3)->andReturn(array());
        $this->homeService->shouldReceive('defaultTemplate')->once()->andReturn(array());
        $this->templating->shouldReceive('render')->times(2);
        $this->assertEquals(" ", $this->homeManager->getContentByType("home", 1, "left"));
    }

    public function testgetRegions()
    {
        $this->repository->shouldReceive('findAll')->once()->andReturn($this->region);
        $this->assertEquals(array(), $this->homeManager->getRegions());
    }

    public function testgetTypes()
    {
        $this->repository->shouldReceive('findAll')->once()->andReturn($this->type);
        $this->assertEquals($this->type, $this->homeManager->getTypes());
    }

    public function testgetCreator()
    {
        $this->homeService->shouldReceive('isDefinedPush')->once()->andReturn(array());
        $this->security->shouldReceive('isGranted')->with('ROLE_ADMIN')->once()->andReturn(true);
        $this->homeService->shouldReceive('defaultTemplate')->once()->andReturn(array());
        $this->templating->shouldReceive('render')->once();
        $this->assertEquals(new Response(), $this->homeManager->getCreator('home', null, null, null));
    }

    public function testgetGraph()
    {
        $this->graph->shouldReceive('get')->once()->andReturn(array('type' => 'video'));
        $this->homeService->shouldReceive('defaultTemplate')->once()->andReturn(array());
        $this->templating->shouldReceive('render')->once();
        $this->assertEquals(new Response(), $this->homeManager->getGraph("http://youtu.be/tmauTTi7awA"));
    }

    public function testcreateContent()
    {
        $this->writer->shouldReceive('suspendFlush')->once();
        $this->writer->shouldReceive('create')->times(2);
        $this->writer->shouldReceive('forceFlush')->once();
        $this->repository->shouldReceive('find')->once()->andReturn($this->content);
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->subContent);
        $this->subContent->shouldReceive('setBack')->once();

        $this->assertEquals(
            new Response(),
            $this->homeManager->createContent('title', 'some content', 'foo', 'home', 1)
        );
    }

    public function testupdateContent()
    {
        $this->writer->shouldReceive('suspendFlush')->once();
        $this->writer->shouldReceive('update')->times(2);
        $this->writer->shouldReceive('forceFlush')->once();
        $this->repository->shouldReceive('findOneBy')->times(2)->andReturn($this->type, $this->contentType);
        $this->content->shouldReceive('setTitle')->once();
        $this->content->shouldReceive('setContent')->once();
        $this->content->shouldReceive('setGeneratedContent')->once();
        $this->content->shouldReceive('setModified')->once();
        $this->contentType->shouldReceive('setSize')->once();

        $this->assertEquals(
            new Response("true"),
            $this->homeManager->updateContent($this->content, 'title', 'text', 'foo', 'span12', 'home')
        );
    }

    public function testreorderContent()
    {
        $this->repository->shouldReceive('findOneBy')->times(2)->andReturn($this->contentType);
        $this->contentType->shouldReceive('detach')->once();
        $this->contentType->shouldReceive('getBack')->times(2);
        $this->contentType->shouldReceive('setBack')->times(2);
        $this->contentType->shouldReceive('setNext')->once();
        $this->writer->shouldReceive('update')->times(2);

        $this->assertEquals(
            new Response("true"),
            $this->homeManager->reorderContent($this->type, $this->content, $this->content)
        );
    }

    public function testdeleteContent()
    {
        $this->repository->shouldReceive('findBy')->times(4)->andReturn(
            $this->contentType, $this->subContent, $this->subContent, $this->contentType
        );
        $this->writer->shouldReceive('delete')->once();
        $this->assertEquals(new Response("true"), $this->homeManager->deleteContent($this->content));
    }

    public function testdeleNodeEntity()
    {
        $this->repository->shouldReceive('findBy')->once()->andReturn($this->contentType);
        $this->homeManager->deleNodeEntity($this->repository, array('id' => 1), null);
    }

    public function testcontentToRegion()
    {
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->contentRegion);
        $this->writer->shouldReceive('create')->once();
        $this->contentRegion->shouldReceive('setBack')->once();

        $this->assertEquals(new Response("true"), $this->homeManager->contentToRegion($this->region, $this->content));
    }
}


