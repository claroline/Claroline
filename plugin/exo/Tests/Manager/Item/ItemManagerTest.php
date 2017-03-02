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

    protected function setUp()
    {
        parent::setUp();

        $this->manager = $this->client->getContainer()->get('ujm_exo.manager.item');
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
     * @expectedException \UJM\ExoBundle\Library\Validator\ValidationException
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
     * @expectedException \UJM\ExoBundle\Library\Validator\ValidationException
     */
    public function testUpdateWithInvalidData()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
