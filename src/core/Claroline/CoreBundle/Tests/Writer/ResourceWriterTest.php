<?php

namespace Claroline\CoreBundle\Writer;

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class ResourceWriterTest extends FixtureTestCase
{
    /** @var ResourceWriter */
    private $writer;
    private $resourceRepo;
    private $dirType;

    public function setUp()
    {
        parent::setup();

        $this->writer = $this->client->getContainer()->get('claroline.writer.resource_writer');
        $this->resourceRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $this->dirType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('directory');
    }

    public function testCreate()
    {
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user'), false);
        $ws = $this->client->getContainer()->get('claroline.writer.workspace_writer')->create('name', 'code', true);
        $icons = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findAll();
        $prevRes = count($this->resourceRepo->findAll());
        $dir = new Directory();
        $this->writer->create(
            $dir,
            $this->dirType,
            $this->getUser('user'),
            $ws,
            'dir',
            $icons[0]
        );
        $nextRes = count($this->resourceRepo->findAll());
        $this->assertEquals(1, $nextRes - $prevRes);
    }

    public function testSetOrder()
    {
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user'));
        $ws = $this->client->getContainer()->get('claroline.writer.workspace_writer')->create('name', 'code', true);
        $icons = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findAll();

        $dir = new Directory();
        $this->writer->create(
            $dir,
            $this->dirType,
            $this->getUser('user'),
            $ws,
            'dir',
            $icons[0]
        );

        $previous = new Directory();
        $this->writer->create(
            $previous,
            $this->dirType,
            $this->getUser('user'),
            $ws,
            'dir2',
            $icons[0]
        );

        $next = new Directory();
        $this->writer->create(
            $next,
            $this->dirType,
            $this->getUser('user'),
            $ws,
            'dir3',
            $icons[0]
        );

        $this->writer->setOrder($dir, $previous, $next);
        $this->assertEquals($previous, $dir->getPrevious());
        $this->assertEquals($next, $dir->getNext());
        $this->writer->setOrder($dir, null, null);
        $this->assertEquals(null, $dir->getPrevious());
        $this->assertEquals(null, $dir->getNext());
    }

    public function testMove()
    {
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user'));
        $ws = $this->client->getContainer()->get('claroline.writer.workspace_writer')->create('name', 'code', true);
        $icons = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findAll();

        $oldDad = new Directory();
        $this->writer->create(
            $oldDad,
            $this->dirType,
            $this->getUser('user'),
            $ws,
            'dir',
            $icons[0]
        );

        $newDad = new Directory();
        $this->writer->create(
            $newDad,
            $this->dirType,
            $this->getUser('user'),
            $ws,
            'dir2',
            $icons[0]
        );

        $child = new Directory();
        $this->writer->create(
            $child,
            $this->dirType,
            $this->getUser('user'),
            $ws,
            'dir3',
            $icons[0],
            $oldDad
        );

        $this->writer->move($child, $newDad, 'name');
        $this->assertEquals($newDad, $child->getParent());
    }
}