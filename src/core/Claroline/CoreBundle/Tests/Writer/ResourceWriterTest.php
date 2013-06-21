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
}