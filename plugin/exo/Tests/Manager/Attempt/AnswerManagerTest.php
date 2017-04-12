<?php

namespace UJM\ExoBundle\Tests\Manager\Attempt;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Manager\Attempt\AnswerManager;
use UJM\ExoBundle\Serializer\Attempt\AnswerSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerValidator;

class AnswerManagerTest extends TransactionalTestCase
{
    /** @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    private $om;
    /** @var AnswerValidator|\PHPUnit_Framework_MockObject_MockObject */
    private $validator;
    /** @var AnswerSerializer|\PHPUnit_Framework_MockObject_MockObject */
    private $serializer;
    /** @var AnswerManager */
    private $manager;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->validator = $this->mock('UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerValidator');
        $this->serializer = $this->mock('UJM\ExoBundle\Serializer\Attempt\AnswerSerializer');

        $this->manager = new AnswerManager($this->om, $this->validator, $this->serializer);
    }

    public function testSerialize()
    {
        $answer = new Answer();
        $options = [
            'an array of options',
        ];

        // Checks the serializer is called
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($answer, $options)
            ->willReturn(new \stdClass());

        $data = $this->manager->serialize($answer, $options);

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
