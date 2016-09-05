<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\LinkHintPaper;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Repository\HintRepository;
use UJM\ExoBundle\Repository\PaperRepository;

/**
 * @DI\Service("ujm.exo.hint_manager")
 */
class HintManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @DI\InjectParams({
     *     "objectManager"   = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(
        ObjectManager $objectManager)
    {
        $this->om = $objectManager;
    }

    /**
     * Export an Hint.
     *
     * @param Hint $hint
     * @param bool $withSolution
     *
     * @return \stdClass
     */
    public function exportHint(Hint $hint, $withSolution = false)
    {
        $hintData = new \stdClass();
        $hintData->id = (string) $hint->getId();
        $hintData->penalty = $hint->getPenalty();

        if ($withSolution) {
            $hintData->value = $hint->getValue();
        }

        return $hintData;
    }

    /**
     * Returns whether a hint is related to a paper.
     *
     * @param Paper $paper
     * @param Hint  $hint
     *
     * @return bool
     */
    public function hasHint(Paper $paper, Hint $hint)
    {
        /** @var PaperRepository $repo */
        $repo = $this->om->getRepository('UJMExoBundle:Paper');

        return $repo->hasHint($paper, $hint);
    }

    /**
     * Returns the contents of a hint and records a log asserting that the hint
     * has been consulted for a given paper.
     *
     * @param Paper $paper
     * @param Hint  $hint
     *
     * @return string
     */
    public function viewHint(Paper $paper, Hint $hint)
    {
        $log = $this->om->getRepository('UJMExoBundle:LinkHintPaper')
            ->findOneBy(['paper' => $paper, 'hint' => $hint]);

        if (!$log) {
            $log = new LinkHintPaper($hint, $paper);
            $this->om->persist($log);
            $this->om->flush();
        }

        return $hint->getValue();
    }

    /**
     * Get Hints used by a User for a Question.
     *
     * @param Paper    $paper
     * @param Question $question
     *
     * @return Hint[]
     */
    public function getUsedHints(Paper $paper, Question $question)
    {
        /** @var HintRepository $repo */
        $repo = $this->om->getRepository('UJMExoBundle:Hint');

        return $repo->findViewedByPaperAndQuestion($paper, $question);
    }

    /**
     * Get score penalty for a Question based on Hints used by the User.
     *
     * @param Paper    $paper
     * @param Question $question
     *
     * @return float
     */
    public function getPenalty(Paper $paper, Question $question)
    {
        $penalty = 0;
        $usedHints = $this->getUsedHints($paper, $question);
        foreach ($usedHints as $used) {
            $penalty += $used->getPenalty();
        }

        return $penalty;
    }
}
