<?php

namespace UJM\ExoBundle\Tests\Manager\Attempt;

use Claroline\AppBundle\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Serializer\Attempt\PaperSerializer;

class PaperManagerTest extends TestCase
{
    /** @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    private $om;
    /** @var PaperSerializer|\PHPUnit_Framework_MockObject_MockObject */
    private $serializer;
    /** @var PaperManager */
    private $manager;

    protected function setUp(): void
    {
        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->serializer = $this->mock('UJM\ExoBundle\Serializer\Attempt\PaperSerializer');

        $this->manager = new PaperManager(
            $this->mock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface'),
            $this->om,
            $this->mock('Symfony\Component\EventDispatcher\EventDispatcherInterface'),
            $this->serializer,
            $this->mock('UJM\ExoBundle\Manager\Item\ItemManager'),
            $this->mock('UJM\ExoBundle\Manager\Attempt\ScoreManager'),
            $this->mock('Claroline\EvaluationBundle\Manager\ResourceEvaluationManager')
        );
    }

    public function testSerialize()
    {
        $paper = new Paper();
        $exercise = new Exercise();
        $paper->setExercise($exercise);
        $options = [
            'an array of options',
        ];

        // Checks the serializer is called
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($paper, $options)
            ->willReturn(new \stdClass());

        $data = $this->manager->serialize($paper, $options);

        // Checks the result of the serializer is returned
        $this->assertInstanceOf('\stdClass', $data);
    }

    private function mock($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
