<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Repository\ExerciseRepository;
use UJM\ExoBundle\Repository\PaperRepository;

/**
 * @DI\Service("ujm_exo.manager.docimology")
 */
class DocimologyManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ExerciseRepository
     */
    private $exerciseRepository;

    /**
     * @var PaperRepository
     */
    private $paperRepository;

    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var PaperManager
     */
    private $paperManager;

    /**
     * ExerciseManager constructor.
     *
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "itemManager" = @DI\Inject("ujm_exo.manager.item"),
     *     "paperManager" = @DI\Inject("ujm_exo.manager.paper")
     * })
     *
     * @param ObjectManager $om
     * @param ItemManager   $itemManager
     * @param PaperManager  $paperManager
     */
    public function __construct(
          ObjectManager $om,
          ItemManager $itemManager,
          PaperManager $paperManager
    ) {
        $this->om = $om;
        $this->exerciseRepository = $this->om->getRepository('UJMExoBundle:Exercise');
        $this->paperRepository = $this->om->getRepository('UJMExoBundle:Attempt\Paper');
        $this->paperManager = $paperManager;
        $this->itemManager = $itemManager;
    }

    /**
     * Serializes an Exercise.
     *
     * @param Exercise $exercise
     * @param float    $maxScore
     *
     * @return \stdClass
     */
    public function getStatistics(Exercise $exercise, $maxScore)
    {
        $statistics = new \stdClass();
        $statistics->maxScore = $maxScore;
        $statistics->nbSteps = $exercise->getSteps()->count();
        $statistics->nbQuestions = $this->exerciseRepository->countExerciseQuestion($exercise);
        $statistics->nbPapers = $this->paperManager->countExercisePapers($exercise);
        $statistics->nbRegisteredUsers = $this->paperManager->countPapersUsers($exercise);
        $statistics->nbAnonymousUsers = $this->paperManager->countAnonymousPapers($exercise);
        $statistics->minMaxAndAvgScores = $this->getMinMaxAverageScores($exercise, $maxScore);
        $statistics->paperSuccessDistribution = $this->getPapersSuccessDistribution($exercise, $maxScore);
        $statistics->paperScoreDistribution = $this->getPaperScoreDistribution($exercise, $maxScore);
        $statistics->questionsDifficultyIndex = $this->getQuestionsDifficultyIndex($exercise);
        $statistics->discriminationCoefficient = $this->getDiscriminationCoefficient($exercise);

        return $statistics;
    }

    /**
     * Returns the max min and average score for a given exercise.
     *
     * @param Exercise $exercise
     * @param float    $scoreOn
     *
     * @return \stdClass
     */
    public function getMinMaxAverageScores(Exercise $exercise, $scoreOn)
    {
        $papers = $this->paperRepository->findBy([
            'exercise' => $exercise,
        ]);

        $scores = $this->getPapersScores($papers, $scoreOn);

        $result = new \stdClass();
        $result->min = count($scores) === 0 ? 0 : min($scores);
        $result->max = count($scores) === 0 ? 0 : max($scores);
        $average = count($scores) === 0 ? 0 : array_sum($scores) / count($scores);
        $result->avg = $average !== floor($average) ? floatval(number_format($average, 2)) : $average;

        return $result;
    }

    /**
     * Returns the number of fully, partially successfull and missed papers for a given exercise.
     *
     * @param Exercise $exercise
     * @param float    $scoreOn
     *
     * @return \stdClass
     */
    public function getPapersSuccessDistribution(Exercise $exercise, $scoreOn)
    {
        $papers = $this->paperRepository->findBy([
            'exercise' => $exercise,
        ]);

        $nbSuccess = 0;
        $nbMissed = 0;
        $nbPartialSuccess = 0;

        $scores = $this->getPapersScores($papers, $scoreOn);

        /* @var Paper $paper */
        foreach ($scores as $score) {
            if ($score === floatval(0)) {
                ++$nbMissed;
            } elseif ($score === floatval($scoreOn)) {
                ++$nbSuccess;
            } else {
                ++$nbPartialSuccess;
            }
        }

        $papersSuccessDistribution = new \stdClass();
        $papersSuccessDistribution->nbSuccess = $nbSuccess;
        $papersSuccessDistribution->nbMissed = $nbMissed;
        $papersSuccessDistribution->nbPartialSuccess = $nbPartialSuccess;

        return $papersSuccessDistribution;
    }

    /**
     * Returns the number of papers with a particular score for a given exercise.
     *
     * @param Exercise $exercise
     * @param float    $scoreOn
     *
     * @return \stdClass
     */
    public function getPaperScoreDistribution(Exercise $exercise, $scoreOn)
    {
        $papers = $this->paperRepository->findBy([
            'exercise' => $exercise,
        ]);

        $scores = $this->getPapersScores($papers, $scoreOn);

        // get unique scores from scores array
        $uniqueScores = array_unique($scores, SORT_NUMERIC);
        sort($uniqueScores);

        $paperScoreDistribution = new \stdClass();
        foreach ($uniqueScores as $key) {
            $matchingScores = array_filter($scores, function ($score) use ($key) {
                return floatval($score) === floatval($key);
            });
            $statsData = new \stdClass();
            $statsData->yData = count($matchingScores);
            $statsData->xData = $key;

            $paperScoreDistribution->{$key} = $statsData;
        }

        return $paperScoreDistribution;
    }

    public function getQuestionsDifficultyIndex(Exercise $exercise)
    {
        $papers = $this->paperRepository->findBy([
            'exercise' => $exercise,
        ]);

        $questionStatistics = [];
        $itemRepository = $this->om->getRepository('UJMExoBundle:Item\Item');

        /** @var Paper $paper */
        foreach ($papers as $paper) {
            // base success compution on paper structure
            $structure = json_decode($paper->getStructure());
            foreach ($structure->steps as $step) {
                foreach ($step->items as $item) {
                    // since the compution is based on the structure the same item can come several times
                    if (!array_key_exists($item->id, $questionStatistics)) {
                        $itemEntity = $itemRepository->findOneBy(['uuid' => $item->id]);
                        $questionStats = $this->itemManager->getStatistics($itemEntity, $exercise);
                        $questionData = new \stdClass();
                        $questionData->yData = $questionStats->successPercent;
                        $questionData->xData = $itemEntity->getTitle() ? strip_tags($itemEntity->getTitle()) : strip_tags($itemEntity->getContent());
                        $questionStatistics[$item->id] = $questionData;
                    }
                }
            }
        }

        return $questionStatistics;
    }

    /**
     * Get discrimination coefficient for an exercise.
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    private function getDiscriminationCoefficient(Exercise $exercise)
    {
        // get all scores for the exercise
        $papers = $this->paperRepository->findBy([
            'exercise' => $exercise,
        ]);

        $itemRepository = $this->om->getRepository('UJMExoBundle:Item\Item');
        $reportScoreOn = 100;
        // all scores obtained for an exercise
        $exerciseScores = $this->getPapersScores($papers, $reportScoreOn);
        // exercise standard deviation
        $standardDeviationE = $this->getStandardDeviation($exerciseScores);
        // get average score per exercise
        $exerciseAverageScore = $this->getMinMaxAverageScores($exercise, $reportScoreOn)->avg;
        // all scores obtained per question
        $questionsScores = [];
        // all average scores per question
        $questionsAvgScores = [];
        // all margin marks per question
        $questionsMarginMark = [];
        // computed result to return
        $discriminationCoef = [];

        /** @var Paper $paper */
        foreach ($papers as $paper) {
            // base success compution on paper structure
            $structure = json_decode($paper->getStructure());
            foreach ($structure->steps as $step) {
                foreach (array_filter($step->items, function ($item) {
                    return ItemType::isSupported($item->type);
                }) as $item) {
                    // since the compution is based on the structure the same item can come several times
                    if (!array_key_exists($item->id, $discriminationCoef)) {
                        $itemEntity = $itemRepository->findOneBy(['uuid' => $item->id]);
                        // set questions scores
                        $questionsScores[$item->id] = $this->itemManager->getItemScores($exercise, $itemEntity);
                        // get average score for the item
                        $questionsAvgScores[$item->id] = array_sum($questionsScores[$item->id]) / count($questionsScores[$item->id]);
                        $i = 0;
                        foreach ($questionsScores[$item->id] as $score) {
                            $questionsMarginMark[$item->id][] = ($score - $questionsAvgScores[$item->id]) * ($exerciseScores[$i] - $exerciseAverageScore);
                            ++$i;
                        }

                        $questionProductMarginMark = $questionsMarginMark[$item->id];
                        $sumPenq = array_sum($questionProductMarginMark);
                        $sumPenq = round($sumPenq, 3);
                        $standardDeviationQ = $this->getStandardDeviation($questionsScores[$item->id]);
                        $n = count($questionProductMarginMark);
                        $nSxSy = $n * $standardDeviationQ * $standardDeviationE;
                        $coef = $nSxSy === floatval(0) ? 0 : round($sumPenq / ($nSxSy), 3);

                        $itemDiscriminationCoef = new \stdClass();
                        $itemDiscriminationCoef->xData = $itemEntity->getTitle() ? strip_tags($itemEntity->getTitle()) : strip_tags($itemEntity->getContent());
                        $itemDiscriminationCoef->yData = $coef;
                        $discriminationCoef[$item->id] = $itemDiscriminationCoef;
                    }
                }
            }
        }

        return $discriminationCoef;
    }

    /**
     * Get standard deviation for the discrimination coefficient.
     *
     * @param type $array
     *
     * @return type
     */
    private function getStandardDeviation($array)
    {
        $sdSquare = function ($x, $mean) {
            return pow($x - $mean, 2);
        };
        // avoid division by 0
        $nbData = count($array) > 1 ? count($array) - 1 : 1;
        $fillNbData = count($array) > 0 ? count($array) : 1;

        return sqrt(
          array_sum(
            array_map(
              $sdSquare,
              $array,
              array_fill(0, count($array), (array_sum($array) / $fillNbData))
            )
          ) / $nbData
        );
    }

    /**
     * Get scores for a paper.
     * If $scoreOn is not null then all scores are reported on this value.
     *
     * @param Exercise $exercise
     * @param float    $scoreOn
     *
     * @return array
     */
    private function getPapersScores($papers, $scoreOn = null)
    {
        $scores = [];
        /** @var Paper $paper */
        foreach ($papers as $paper) {
            $structure = json_decode($paper->getStructure());
            if (!isset($structure->parameters->totalScoreOn) || floatval($structure->parameters->totalScoreOn) === floatval(0)) {
                $totalScoreOn = $this->paperManager->calculateTotal($paper);
            } else {
                $totalScoreOn = floatval($structure->parameters->totalScoreOn);
            }

            $score = $this->paperManager->calculateScore($paper, $totalScoreOn);
            // since totalScoreOn might have change through papers report all scores on a define value
            if ($scoreOn && $totalScoreOn > 0) {
                $score = floatval(($scoreOn * $score) / $totalScoreOn);
            }
            $scores[] = $score !== floor($score) ? floatval(number_format($score, 2)) : $score;
        }

        return $scores;
    }
}
