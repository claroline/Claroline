<?php

namespace Claroline\CoreBundle\Writer;

use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class WorkspaceWriterTest extends FixtureTestCase
{
    /** @var WorkspaceWriter */
    private $writer;
    private $workspaceRepo;

    public function setUp()
    {
        parent::setup();

        $this->writer = $this->client->getContainer()->get('claroline.writer.workspace_writer');
        $this->workspaceRepo = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
    }

    public function testCreate()
    {
        $this->writer->create('name', 'code', true);
        $this->assertEquals(1, count($this->workspaceRepo->findAll()));
    }
}