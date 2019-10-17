<?php

namespace UJM\ExoBundle\Tests\Manager\Item;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use UJM\ExoBundle\Manager\Item\ItemManager;

class ItemManagerTest extends TransactionalTestCase
{
    /**
     * @var ItemManager
     */
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->client->getContainer()->get('UJM\ExoBundle\Manager\Item\ItemManager');
    }

    public function testExport()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testCreate()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @expectedException \Claroline\CoreBundle\Validator\Exception\InvalidDataException
     */
    public function testCreateWithInvalidData()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testUpdate()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @expectedException \Claroline\CoreBundle\Validator\Exception\InvalidDataException
     */
    public function testUpdateWithInvalidData()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
