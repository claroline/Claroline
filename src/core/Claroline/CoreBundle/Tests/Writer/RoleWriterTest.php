<?php

namespace Claroline\CoreBundle\Writer;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class RoleWriterTest extends FixtureTestCase
{
    /** @var RoleWriter */
    private $writer;
    private $roleRepo;

    public function setUp()
    {
        parent::setup();

        $this->writer = $this->client->getContainer()->get('claroline.writer.role_writer');
        $this->roleRepo = $this->em->getRepository('ClarolineCoreBundle:Role');
    }

    public function testCreate()
    {
        $roles = $this->writer->create('ROLE_HELLO', 'translation', Role::BASE_ROLE);
        $this->assertEquals(1, count($roles));
    }

    public function testBind()
    {
        //create group
        //crerte user
    }
}