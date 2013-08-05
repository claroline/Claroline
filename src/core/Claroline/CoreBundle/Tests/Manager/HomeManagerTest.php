<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Manager\HomeManager;
use Claroline\CoreBundle\Entity\Home\Type;

class HomeManagerTest extends MockeryTestCase
{
    private $graph;
    private $manager;
    private $registry;
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

        $this->graph = $this->mock('Claroline\CoreBundle\Library\Home\GraphService');
        $this->manager = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->registry = $this->mock('Doctrine\Bundle\DoctrineBundle\Registry');
        $this->homeService = $this->mock('Claroline\CoreBundle\Library\Home\HomeService');
        $this->repository = $this->mock('Doctrine\ORM\EntityRepository');

        $this->type = $this->mock('Claroline\CoreBundle\Entity\Home\Type');
        $this->region = $this->mock('Claroline\CoreBundle\Entity\Home\Region');
        $this->content = $this->mock('Claroline\CoreBundle\Entity\Home\Content');
        $this->subContent = $this->mock('Claroline\CoreBundle\Entity\Home\SubContent');
        $this->contentType = $this->mock('Claroline\CoreBundle\Entity\Home\Content2Type');
        $this->contentRegion = $this->mock('Claroline\CoreBundle\Entity\Home\Content2Region');

        $this->registry->shouldReceive('getRepository')->times(6)->andReturn($this->repository);

        $this->homeManager = new HomeManager(
            $this->graph, $this->homeService, $this->registry, $this->manager
        );
    }

    public function testgetContent()
    {
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->contentType);
        $this->type->shouldReceive('getName')->once();
        $this->contentType->shouldReceive('getSize')->once();
        $this->assertEquals(
            array('type' => null, 'size' => null, 'content' => $this->content),
            $this->homeManager->getContent($this->content, $this->type, null)
        );
    }

    public function testcontentLayout()
    {
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->type);
        $this->repository->shouldReceive('find')->once()->andReturn($this->content);
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->subContent);
        $this->type->shouldReceive('getMaxContentPage')->times(2)->andReturn(1);
        $this->type->shouldReceive('getName')->once();
        $this->subContent->shouldReceive('getContent')->once()->andReturn($this->content);
        $this->subContent->shouldReceive('getSize')->once();
        $this->subContent->shouldReceive('getNext')->once();
        $this->homeService->shouldReceive('isDefinedPush')->times(4)->andReturn(array());
        $this->assertEquals(array(), $this->homeManager->contentLayout('home', 1, 'left'));
    }

    public function testgetContentByType()
    {
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->type);
        $this->repository->shouldReceive('find')->once()->andReturn($this->content);
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->subContent);
        $this->type->shouldReceive('getMaxContentPage')->times(2)->andReturn(1);
        $this->type->shouldReceive('getName')->once();
        $this->subContent->shouldReceive('getContent')->once()->andReturn($this->content);
        $this->subContent->shouldReceive('getSize')->once();
        $this->subContent->shouldReceive('getNext')->once();
        $this->homeService->shouldReceive('isDefinedPush')->times(2)->andReturn(array());
        $this->assertEquals(array(array()), $this->homeManager->getContentByType('home', 1, 'left'));
    }

    public function testgetRegionContents()
    {
        $this->repository->shouldReceive('findAll')->once()->andReturn($this->region);
        $this->assertEquals(array(), $this->homeManager->getRegionContents());
    }

    public function testgetTypes()
    {
        $this->repository->shouldReceive('findAll')->once()->andReturn($this->type);
        $this->assertEquals($this->type, $this->homeManager->getTypes());
    }

    public function testgetGraph()
    {
        $array = array('type' => 'video');
        $this->graph->shouldReceive('get')->once()->andReturn($array);
        $this->assertEquals($array, $this->homeManager->getGraph('http://youtu.be/tmauTTi7awA'));
    }

    public function testcreateContent()
    {
        $this->manager->shouldReceive('persist')->times(2);
        $this->manager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('find')->once()->andReturn($this->content);
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->subContent);
        $this->subContent->shouldReceive('setBack')->once();
        $this->assertEquals(null, $this->homeManager->createContent('title', 'some content', 'foo', 'home', 1));
    }

    public function testupdateContent()
    {
        $this->manager->shouldReceive('persist')->times(2);
        $this->manager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('findOneBy')->times(2)->andReturn($this->type, $this->contentType);
        $this->content->shouldReceive('setTitle')->once();
        $this->content->shouldReceive('setContent')->once();
        $this->content->shouldReceive('setGeneratedContent')->once();
        $this->content->shouldReceive('setModified')->once();
        $this->contentType->shouldReceive('setSize')->once();
        $this->assertEquals(
            null, $this->homeManager->updateContent($this->content, 'title', 'text', 'foo', 'span12', 'home')
        );
    }

    public function testreorderContent()
    {
        $this->repository->shouldReceive('findOneBy')->times(2)->andReturn($this->contentType);
        $this->contentType->shouldReceive('detach')->once();
        $this->contentType->shouldReceive('getBack')->times(2);
        $this->contentType->shouldReceive('setBack')->times(2);
        $this->contentType->shouldReceive('setNext')->once();
        $this->manager->shouldReceive('persist')->times(2);
        $this->manager->shouldReceive('flush')->once();
        $this->assertEquals(null, $this->homeManager->reorderContent($this->type, $this->content, $this->content));
    }

    public function testdeleteContent()
    {
        $this->repository->shouldReceive('findBy')->times(4)->andReturn(
            $this->contentType, $this->subContent, $this->subContent, $this->contentType
        );
        $this->manager->shouldReceive('remove')->once();
        $this->manager->shouldReceive('flush')->once();
        $this->assertEquals(null, $this->homeManager->deleteContent($this->content));
    }

    public function testdeleteType()
    {
        $this->repository->shouldReceive('findBy')->times(4)->andReturn(
            $this->contentType, $this->subContent, $this->subContent, $this->contentType
        );
        $this->manager->shouldReceive('remove')->once();
        $this->manager->shouldReceive('flush')->once();
        $this->assertEquals(null, $this->homeManager->deleteType($this->type));
    }

    public function testcreateType()
    {
        $this->repository->shouldReceive('findOneBy')->once();
        $this->manager->shouldReceive('persist')->once();
        $this->manager->shouldReceive('flush')->once();
        $this->assertEquals(new Type('home'), $this->homeManager->createType('home'));
    }

    public function testtypeExist()
    {
        $this->repository->shouldReceive('findOneBy')->once();
        $this->assertEquals('false', $this->homeManager->typeExist('home'));
    }

    public function testdeleNodeEntity()
    {
        $this->repository->shouldReceive('findBy')->once()->andReturn($this->contentType);
        $this->assertEquals(null, $this->homeManager->deleNodeEntity($this->repository, array('id' => 1), null));
    }

    public function testcontentToRegion()
    {
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->contentRegion);
        $this->manager->shouldReceive('persist')->once();
        $this->manager->shouldReceive('flush')->once();
        $this->contentRegion->shouldReceive('setBack')->once();
        $this->assertEquals(null, $this->homeManager->contentToRegion($this->region, $this->content));
    }

    public function testgetCreator()
    {
        $this->homeService->shouldReceive('isDefinedPush')->once()->andReturn(array());
        $this->assertEquals(array(), $this->homeManager->getCreator('home', null, null, null));
    }

    public function testgetMenu()
    {
        $this->homeService->shouldReceive('isDefinedPush')->once()->andReturn(array());
        $this->assertEquals(array(), $this->homeManager->getMenu(1, 'span12', 'home', null));
    }
}
