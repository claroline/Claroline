<?php

namespace Claroline\CoreBundle\Writer;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\Entity\Role;

class ToolWriterTest extends FixtureTestCase
{
    /** @var ToolWriter */
    private $writer;
    private $toolRepo;
    private $orderedToolRepo;
    private $roleWriter;

    public function setUp()
    {
        parent::setup();

        $this->writer = $this->client->getContainer()->get('claroline.writer.tool_writer');
        $this->roleWriter = $this->client->getContainer()->get('claroline.writer.role_writer');
        $this->toolRepo = $this->client->getContainer()->get('tool_repository');
        $this->orderedToolRepo = $this->client->getContainer()->get('ordered_tool_repository');
    }

    public function testCreate()
    {
        $prev = count($this->toolRepo->findAll());
        $this->writer->create(
            'name',
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true
        );
        $next = count($this->toolRepo->findAll());
        $this->assertEquals(1, $next - $prev);
    }

    public function testCreateOrderedTool()
    {
        $tool = $this->writer->create(
            'name',
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true
        );

        $prev = count($this->orderedToolRepo->findAll());
        $this->writer->createOrderedTool('hello', 1, $tool);
        $next = count($this->orderedToolRepo->findAll());
        $this->assertEquals(1, $next - $prev);
    }

    public function testAddRole()
    {
        $tool = $this->writer->create(
            'name',
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true
        );
        $ordered = $this->writer->createOrderedTool('hello', 1, $tool);
        $role = $this->roleWriter->create('ROLE_HELLO', 'translation', Role::BASE_ROLE);
        $ordered->addRole($role);
        $this->em->clear();
        $dbRes = $this->orderedToolRepo->findOneByName('hello');
        $roles = $dbRes->getRoles();
        var_dump(get_class($roles));

        $this->assertTrue($roles->contains($role));
    }

    public function testRemoveRole()
    {

    }

}