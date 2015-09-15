<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use UJM\ExoBundle\Entity\Exercise;

class ExerciseManagerTest extends \PHPUnit_Framework_TestCase
{
    private $om;
    private $manager;

    protected function setUp()
    {
        $this->om = $this->getMockBuilder('Claroline\CoreBundle\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = new ExerciseManager($this->om);
    }

    /**
     * @expectedException \LogicException
     */
    public function testPublishThrowsIfExerciseIsPublished()
    {
        $node = new ResourceNode();
        $node->setPublished(true);
        $exercise = new Exercise();
        $exercise->setResourceNode($node);

        $this->manager->publish($exercise);
    }

    public function testPublishOncePublishedExercise()
    {
        $node = new ResourceNode();
        $node->setPublished(false);
        $exercise = new Exercise();
        $exercise->setPublishedOnce(true);
        $exercise->setResourceNode($node);

        $this->om->expects($this->once())->method('flush');

        $this->manager->publish($exercise);

        $this->assertTrue($node->isPublished());
    }

    public function testPublishNeverPublishedExerciseDeleteItsPapers()
    {
        $this->markTestSkipped('Not implemented yet');
    }

    /**
     * @expectedException \LogicException
     */
    public function testPublishThrowsIfExerciseIsUnpublished()
    {
        $node = new ResourceNode();
        $node->setPublished(false);
        $exercise = new Exercise();
        $exercise->setResourceNode($node);

        $this->manager->unpublish($exercise);
    }

    public function testUnpublish()
    {
        $node = new ResourceNode();
        $node->setPublished(true);
        $exercise = new Exercise();
        $exercise->setResourceNode($node);

        $this->om->expects($this->once())->method('flush');

        $this->manager->unpublish($exercise);

        $this->assertFalse($node->isPublished());
    }

    public function testPickQuestionsShufflesThemIfNeeded()
    {
        $exercise = new Exercise();
        $exercise->setShuffle(true);

        $this->mockRepository('UJM\ExoBundle\Repository\QuestionRepository')
            ->expects($this->once())
            ->method('findByExercise')
            ->with($exercise)
            ->willReturn([1, 2, 3, 4, 5]);

        $questions = $this->manager->pickQuestions($exercise);
        $this->assertEquals(5, count($questions));
        $this->assertNotEquals([1, 2, 3, 4, 5], $questions);
        $this->assertContains(1, $questions);
        $this->assertContains(2, $questions);
        $this->assertContains(3, $questions);
        $this->assertContains(4, $questions);
        $this->assertContains(5, $questions);
    }

    public function testPickQuestionsDiscardsSomeIfNeeded()
    {
        $exercise = new Exercise();
        $exercise->setNbQuestion(2);

        $this->mockRepository('UJM\ExoBundle\Repository\QuestionRepository')
            ->expects($this->once())
            ->method('findByExercise')
            ->with($exercise)
            ->willReturn([1, 2, 3, 4]);

        $questions = $this->manager->pickQuestions($exercise);
        $this->assertEquals(2, count($questions));
        $this->assertContains($questions[0], [1, 2, 3, 4]);
        $this->assertContains($questions[1], [1, 2, 3, 4]);
    }

    private function mockRepository($entityFqcn)
    {
        $repo = $this->getMockBuilder($entityFqcn)
            ->disableOriginalConstructor()
            ->getMock();
        $this->om->expects($this->once())
            ->method('getRepository')
            ->with('UJMExoBundle:Question')
            ->willReturn($repo);

        return $repo;
    }
}
