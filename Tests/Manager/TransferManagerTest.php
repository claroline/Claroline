<?php

namespace HeVinci\CompetencyBundle\Manager;

use HeVinci\CompetencyBundle\Transfer\JsonValidator;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class TransferManagerTest extends UnitTestCase
{
    private $manager;

    protected function setUp()
    {
        $this->manager = new TransferManager(new JsonValidator());
    }

    public function test()
    {
        $this->markTestSkipped();
    }
}
