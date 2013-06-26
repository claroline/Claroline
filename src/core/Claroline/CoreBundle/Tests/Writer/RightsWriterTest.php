<?php

namespace Claroline\CoreBundle\Writer;

use Claroline\CoreBundle\Entity\Resource\ResourceRights;
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
    private $dirType;

    public function setUp()
    {
        parent::setup();

        $this->writer = $this->client->getContainer()->get('claroline.writer.rights_writer');
        $this->rightsRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->dirType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('directory');

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
            $this->dirType,
            $this->getUser('user'),
            $ws,
            'dir',
            $icons[0]
        );
        $otherDir = new Directory();
        $this->otherResource = $this->client->getContainer()->get('claroline.writer.resource_writer')->create(
            $otherDir,
            $this->dirType,
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
        $copy = $this->writer->createFrom($this->otherResource, $rights);
        $nowCount = count($this->rightsRepo->findAll());
        $this->assertEquals(1, $nowCount - $prevCount);
        $this->assertEquals($rights->getRole(), $copy->getRole());
        $this->assertEquals($this->otherResource, $copy->getResource());
        $this->compareRights($rights, $copy);
    }

    public function testEdit()
    {
        $rights = $this->writer->create($this->permsArray, $this->resourceTypes, $this->resource, $this->role);

        $newPermissions = array(
            'canDelete' => true,
            'canOpen' => false,
            'canEdit' => true,
            'canCopy' => false,
            'canExport' => true,
        );

        $creations = array($this->dirType);
        $this->writer->edit($rights, $newPermissions, $creations);
        $this->comparePermissions($newPermissions, $rights);
        $this->assertEquals(count($creations), count($rights->getCreatableResourceTypes()));

        return $rights;

    }

    private function compareRights(ResourceRights $expected, ResourceRights $result)
    {
        $this->assertEquals($expected->canDelete(), $result->canDelete());
        $this->assertEquals($expected->canOpen(), $result->canOpen());
        $this->assertEquals($expected->canEdit(), $result->canEdit());
        $this->assertEquals($expected->canCopy(), $result->canCopy());
        $this->assertEquals($expected->canExport(), $result->canExport());
        $this->assertEquals(count($expected->getCreatableResourceTypes()), count($result->getCreatableResourceTypes()));
    }

    private function comparePermissions(array $permissions, ResourceRights $rights)
    {
        $this->assertEquals($permissions['canDelete'], $rights->canDelete());
        $this->assertEquals($permissions['canOpen'], $rights->canOpen());
        $this->assertEquals($permissions['canEdit'], $rights->canEdit());
        $this->assertEquals($permissions['canCopy'], $rights->canCopy());
        $this->assertEquals($permissions['canExport'], $rights->canExport());
    }
}