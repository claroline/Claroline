<?php

namespace UJM\ExoBundle\Tests\Manager\Item;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use UJM\ExoBundle\Entity\Item\Category;
use UJM\ExoBundle\Manager\Item\CategoryManager;
use UJM\ExoBundle\Serializer\Item\CategorySerializer;
use UJM\ExoBundle\Validator\JsonSchema\Item\CategoryValidator;

class CategoryManagerTest extends TransactionalTestCase
{
    /** @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    private $om;
    /** @var CategoryValidator|\PHPUnit_Framework_MockObject_MockObject */
    private $validator;
    /** @var CategorySerializer|\PHPUnit_Framework_MockObject_MockObject */
    private $serializer;
    /** @var CategoryManager */
    private $manager;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->validator = $this->mock('UJM\ExoBundle\Validator\JsonSchema\Item\CategoryValidator');
        $this->serializer = $this->mock('UJM\ExoBundle\Serializer\Item\CategorySerializer');

        $this->manager = new CategoryManager($this->om, $this->validator, $this->serializer);
    }

    public function testExport()
    {
        $category = new Category();
        $options = [
            'an array of options',
        ];

        // Checks the serializer is called
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($category, $options)
            ->willReturn(new \stdClass());

        $data = $this->manager->serialize($category, $options);

        // Checks the result of the serializer is returned
        $this->assertInstanceOf('\stdClass', $data);
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

    private function mock($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
