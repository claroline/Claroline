<?php

namespace Claroline\CoreBundle\Writer;

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class RightsWriterTest extends FixtureTestCase
{
    /** @var RightsWriter */
    private $writer;
    private $rightsRepo;
    private $permsArray;
    private $resourceTypes;
    private $role;
    private $resource;
    private $otherResource;

    public function setUp()
    {
        parent::setup();

        $this->writer = $this->client->getContainer()->get('claroline.writer.rights_writer');
        $this->rightsRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');

        $this->permsArray = array(
            'canCopy' => true,
            'canOpen' => false,
            'canDelete' => true,
            'canEdit' => false,
            'canExport' => true
        );

        //all of this is required because a right must have a role and a resource
        $this->resourceTypes = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $this->role = $this->client->getContainer()->get('claroline.writer.role_writer')->create('ROLE_TEST', 'trns', true);
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user'), false);
        $ws = $this->client->getContainer()->get('claroline.writer.workspace_writer')->create('name', 'code', true);
        $icons = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findAll();
        $dir = new Directory();

        $this->resource = $this->client->getContainer()->get('claroline.writer.resource_writer')->create(
            $dir,
            $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('directory'),
            $this->getUser('user'),
            $ws,
            'dir',
            $icons[0]
        );

        $this->otherResource = $this->client->getContainer()->get('claroline.writer.resource_writer')->create(
            $dir,
            $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('directory'),
            $this->getUser('user'),
            $ws,
            'dir',
            $icons[0]
        );
    }

    public function testCreate()
    {
        $prevCount = count($this->rightsRepo->findAll());
        $this->writer->create($this->permsArray, $this->resourceTypes, $this->resource, $this->role);
        $nowCount = count($this->rightsRepo->findAll());
        $this->assertEquals(1, $nowCount - $prevCount);
    }

    public function testCreateFrom()
    {
        $rights = $this->writer->create($this->permsArray, $this->resourceTypes, $this->resource, $this->role);
        $prevCount = count($this->rightsRepo->findAll());
        $this->writer->createFrom($this->otherResource, $rights);
        $nowCount = count($this->rightsRepo->findAll());
        $this->assertEquals(1, $nowCount - $prevCount);
    }
}