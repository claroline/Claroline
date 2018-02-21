<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

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
        $this->manager = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->registry = $this->mock('Doctrine\Bundle\DoctrineBundle\Registry');
        $this->homeService = $this->mock('Claroline\CoreBundle\Library\Home\HomeService');
        $this->repository = $this->mock('Doctrine\ORM\EntityRepository');

        $this->type = $this->mock('Claroline\CoreBundle\Entity\Home\Type');
        $this->region = $this->mock('Claroline\CoreBundle\Entity\Home\Region');
        $this->content = $this->mock('Claroline\CoreBundle\Entity\Content');
        $this->subContent = $this->mock('Claroline\CoreBundle\Entity\Home\SubContent');
        $this->contentType = $this->mock('Claroline\CoreBundle\Entity\Home\Content2Type');
        $this->contentRegion = $this->mock('Claroline\CoreBundle\Entity\Home\Content2Region');

        $this->registry->shouldReceive('getRepository')->times(6)->andReturn($this->repository);

        $this->homeManager = new HomeManager(
            $this->graph, $this->homeService, $this->registry, $this->manager
        );
    }

    public function testGetContent()
    {
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->contentType);
        $this->type->shouldReceive('getName')->once();
        $this->contentType->shouldReceive('getSize')->once();
        $this->assertEquals(
            ['type' => null, 'size' => null, 'content' => $this->content],
            $this->homeManager->getContent($this->content, $this->type, null)
        );
    }

    public function testContentLayout()
    {
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->type);
        $this->repository->shouldReceive('find')->once()->andReturn($this->content);
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->subContent);
        $this->type->shouldReceive('getMaxContentPage')->times(2)->andReturn(1);
        $this->type->shouldReceive('getName')->once();
        $this->subContent->shouldReceive('getContent')->once()->andReturn($this->content);
        $this->subContent->shouldReceive('getSize')->once();
        $this->subContent->shouldReceive('getNext')->once();
        $this->homeService->shouldReceive('isDefinedPush')->times(4)->andReturn([]);
        $this->assertEquals([], $this->homeManager->contentLayout('home', 1, 'left'));
    }

    public function testGetContentByType()
    {
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->type);
        $this->repository->shouldReceive('find')->once()->andReturn($this->content);
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->subContent);
        $this->type->shouldReceive('getMaxContentPage')->times(2)->andReturn(1);
        $this->type->shouldReceive('getName')->once();
        $this->subContent->shouldReceive('getContent')->once()->andReturn($this->content);
        $this->subContent->shouldReceive('getSize')->once();
        $this->subContent->shouldReceive('getNext')->once();
        $this->homeService->shouldReceive('isDefinedPush')->times(2)->andReturn([]);
        $this->assertEquals([[]], $this->homeManager->getContentByType('home', 1, 'left'));
    }

    public function testGetRegionContents()
    {
        $this->repository->shouldReceive('findAll')->once()->andReturn($this->region);
        $this->assertEquals([], $this->homeManager->getRegionContents());
    }

    public function testGetTypes()
    {
        $this->repository->shouldReceive('findAll')->once()->andReturn($this->type);
        $this->assertEquals($this->type, $this->homeManager->getTypes());
    }

    public function testGetGraph()
    {
        $array = ['type' => 'video'];
        $this->graph->shouldReceive('get')->once()->andReturn($array);
        $this->assertEquals($array, $this->homeManager->getGraph('http://youtu.be/tmauTTi7awA'));
    }

    public function testCreateContent()
    {
        $this->manager->shouldReceive('persist')->times(2);
        $this->manager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('find')->once()->andReturn($this->content);
        $this->repository->shouldReceive('findOneBy')->once()->andReturn($this->subContent);
        $this->subContent->shouldReceive('setBack')->once();
        $this->assertEquals(null, $this->homeManager->createContent('title', 'some content', 'foo', 'home', 1));
    }

    public function testUpdateContent()
    {
        $this->manager->shouldReceive('persist')->times(2);
        $this->manager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('findOneBy')->times(2)->andReturn($this->type, $this->contentType);
        $this->content->shouldReceive('setTitle')->once();
        $this->content->shouldReceive('setContent')->once();
        $this->content->shouldReceive('setModified')->once();
        $this->contentType->shouldReceive('setSize')->once();
        $this->assertEquals(
            null, $this->homeManager->updateContent($this->content, 'title', 'text', 'foo', 'col-lg-12', 'home')
        );
    }

    public function testReorderContent()
    {
        $this->repository->shouldReceive('findOneBy')->times(2)->andReturn($this->contentType);
        $this->contentType->shouldReceive('detach')->once();
        $this->contentType->shouldReceive('getBack')->times(2)->andReturn($this->contentType);
        $this->contentType->shouldReceive('setBack')->times(2);
        $this->contentType->shouldReceive('setNext')->once();
        $this->manager->shouldReceive('persist')->times(2);
        $this->manager->shouldReceive('flush')->once();
        $this->assertEquals(null, $this->homeManager->reorderContent($this->type, $this->content, $this->content));
    }

    public function testDeleteContent()
    {
        $this->repository->shouldReceive('findBy')->times(4)->andReturn(
            $this->contentType, $this->subContent, $this->subContent, $this->contentType
        );
        $this->manager->shouldReceive('remove')->once();
        $this->manager->shouldReceive('flush')->once();
        $this->assertEquals(null, $this->homeManager->deleteContent($this->content));
    }

    public function testDeleteType()
    {
        $this->repository->shouldReceive('findBy')->times(4)->andReturn(
            $this->contentType, $this->subContent, $this->subContent, $this->contentType
        );
        $this->manager->shouldReceive('remove')->once();
        $this->manager->shouldReceive('flush')->once();
        $this->assertEquals(null, $this->homeManager->deleteType($this->type));
    }

    public function testCreateType()
    {
        $this->repository->shouldReceive('findOneBy')->once();
        $this->manager->shouldReceive('persist')->once();
        $this->manager->shouldReceive('flush')->once();
        $this->assertEquals(new Type('home'), $this->homeManager->createType('home'));
    }

    public function testTypeExist()
    {
        $this->repository->shouldReceive('findOneBy')->once();
        $this->assertEquals(false, $this->homeManager->typeExist('home'));
    }

    public function testDeleNodeEntity()
    {
        $this->repository->shouldReceive('findBy')->once()->andReturn($this->contentType);
        $this->assertEquals(null, $this->homeManager->deleNodeEntity($this->repository, ['id' => 1], null));
    }

    public function testContentToRegion()
    {
        $this->markTestSkipped();
    }

    public function testGetCreator()
    {
        $this->homeService->shouldReceive('isDefinedPush')->once()->andReturn([]);
        $this->assertEquals([], $this->homeManager->getCreator('home', null, null, null));
    }

    public function testGetMenu()
    {
        $this->homeService->shouldReceive('isDefinedPush')->once()->andReturn([]);
        $this->assertEquals([], $this->homeManager->getMenu(1, 'col-lg-12', 'home', null));
    }
}
