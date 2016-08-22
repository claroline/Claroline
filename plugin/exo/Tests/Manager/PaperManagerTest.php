<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use UJM\ExoBundle\Entity\Paper;

class PaperManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var PaperManager
     */
    private $manager;

    protected function setUp()
    {
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');

        $this->manager = new PaperManager(
            $this->om,
            $this->mock('Symfony\Component\EventDispatcher\EventDispatcherInterface'),
            $this->mock('UJM\ExoBundle\Transfer\Json\QuestionHandlerCollector'),
            $this->mock('UJM\ExoBundle\Manager\QuestionManager'),
            $this->mock('Symfony\Component\Translation\TranslatorInterface'),
            $this->mock('UJM\ExoBundle\Services\classes\PaperService')
        );
    }

    public function testKeepSteps()
    {
        $exercise = $this->mock('UJM\ExoBundle\Entity\Exercise');
        $user = $this->mock('Claroline\CoreBundle\Entity\User');

        $exercise->expects($this->once())
            ->method('getKeepSteps')
            ->willReturn(true);

        // Create the first Paper which will be used to generate order for the next ones
        $paper1 = new Paper();
        $paper1->setUser($user);
        $paper1->setNumPaper(1);
        $paper1->setStart(new \DateTime());
        $paper1->setOrdreQuestion('1;2;3;4');

        // Second time, the repo needs to return us the previous created paper (`paper1`)
        $paperRepo = $this->mock('UJM\ExoBundle\Repository\PaperRepository');

        $this->om->expects($this->once())
            ->method('getRepository')
            ->with('UJMExoBundle:Paper')
            ->willReturn($paperRepo);

        $paperRepo->expects($this->once())
            ->method('findOneBy')
            ->willReturn($paper1);

        $paper2 = $this->manager->createPaper($exercise, $user);

        $this->assertEquals($paper1->getOrdreQuestion(), $paper2->getOrdreQuestion());
    }

    public function testShuffleSteps()
    {
        $this->markTestIncomplete('If the shuffle method returns the original order the assertNotEquals will fail');
        $exercise = $this->mock('UJM\ExoBundle\Entity\Exercise');

        $exercise->expects($this->once())
            ->method('getShuffle')
            ->willReturn(true);

        $exercise->expects($this->once())
            ->method('getSteps')
            ->willReturn(new ArrayCollection([1, 2, 3, 4]));

        $steps = $this->manager->pickSteps($exercise);

        $this->assertEquals(4, count($steps));
        $this->assertNotEquals([1, 2, 3, 4], $steps);
    }

    public function testPickSubsetOfSteps()
    {
        $exercise = $this->mock('UJM\ExoBundle\Entity\Exercise');

        $exercise->expects($this->once())
            ->method('getPickSteps')
            ->willReturn(2);

        $exercise->expects($this->once())
            ->method('getSteps')
            ->willReturn(new ArrayCollection([1, 2, 3, 4]));

        $steps = $this->manager->pickSteps($exercise);

        $this->assertEquals(2, count($steps));
    }

    private function mock($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
