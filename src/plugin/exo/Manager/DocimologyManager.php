<?php

namespace UJM\ExoBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Repository\AnswerRepository;
use UJM\ExoBundle\Repository\ExerciseRepository;
use UJM\ExoBundle\Repository\PaperRepository;

class DocimologyManager
{
    /** @var ObjectManager */
    private $om;

    /** @var ItemManager */
    private $itemManager;

    /** @var PaperManager */
    private $paperManager;

    /** @var ExerciseRepository */
    private $exerciseRepository;

    /** @var PaperRepository */
    private $paperRepository;

    /** @var AnswerRepository */
    private $answerRepository;

    public function __construct(
          ObjectManager $om,
          ItemManager $itemManager,
          PaperManager $paperManager
    ) {
        $this->om = $om;
        $this->paperManager = $paperManager;
        $this->itemManager = $itemManager;

        $this->exerciseRepository = $this->om->getRepository(Exercise::class);
        $this->paperRepository = $this->om->getRepository(Paper::class);
        $this->answerRepository = $this->om->getRepository(Answer::class);
    }

    /**
     * Serializes an Exercise.
     *
     * @param float $maxScore
     *
     * @return array
     */
    public function getStatistics(Exercise $exercise, $maxScore)
    {
        return [
            'maxScore' => $maxScore,
            'nbSteps' => $exercise->getSteps()->count(),
            'nbQuestions' => $this->exerciseRepository->countExerciseQuestion($exercise),
            'nbPapers' => $this->paperManager->countExercisePapers($exercise),
            'nbRegisteredUsers' => $this->paperManager->countUsersPapers($exercise),
            'nbAnonymousUsers' => $this->paperManager->countAnonymousPapers($exercise),
            'minMaxAndAvgScores' => $this->getMinMaxAverageScores($exercise, $maxScore),
            'paperSuccessDistribution' => $this->getPapersSuccessDistribution($exercise, $maxScore),
            'paperScoreDistribution' => $this->getPaperScoreDistribution($exercise, $maxScore),
            'questionsDifficultyIndex' => $this->getQuestionsDifficultyIndex($exercise),
            'discriminationCoefficient' => $this->getDiscriminationCoefficient($exercise),
        ];
    }

    /**
     * Returns the max min and average score for a given exercise.
     *
     * @param float $scoreOn
     *
     * @return array
     */
    public function getMinMaxAverageScores(Exercise $exercise, $scoreOn)
    {
        $papers = $this->paperRepository->findBy([
            'exercise' => $exercise,
        ]);

        $scores = $this->getPapersScores($papers, $scoreOn);

        $result = [
            'min' => 0 === count($scores) ? 0 : min($scores),
            'max' => 0 === count($scores) ? 0 : max($scores),
        ];
        $average = 0 === count($scores) ? 0 : array_sum($scores) / count($scores);
        $result['avg'] = $average !== floor($average) ? floatval(number_format($average, 2)) : $average;

        return $result;
    }

    /**
     * Returns the number of fully, partially successfull and missed papers for a given exercise.
     *
     * @param float $scoreOn
     *
     * @return array
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

        return [
            'nbSuccess' => $nbSuccess,
            'nbMissed' => $nbMissed,
            'nbPartialSuccess' => $nbPartialSuccess,
        ];
    }

    /**
     * Returns the number of papers with a particular score for a given exercise.
     *
     * @param float $scoreOn
     *
     * @return array
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

        $paperScoreDistribution = [];

        foreach ($uniqueScores as $key) {
            $matchingScores = array_filter($scores, function ($score) use ($key) {
                return floatval($score) === floatval($key);
            });

            $paperScoreDistribution[$key] = [
                'yData' => count($matchingScores),
                'xData' => $key,
            ];
        }

        return $paperScoreDistribution;
    }

    public function getQuestionsDifficultyIndex(Exercise $exercise)
    {
        $papers = $this->paperRepository->findBy([
            'exercise' => $exercise,
        ]);

        $questionStatistics = [];
        $itemRepository = $this->om->getRepository(Item::class);

        /** @var Paper $paper */
        foreach ($papers as $paper) {
            // base success computation on paper structure
            $structure = $paper->getStructure(true);

            foreach ($structure['steps'] as $step) {
                foreach ($step['items'] as $item) {
                    // since the computation is based on the structure the same item can come several times
                    if (!array_key_exists($item['id'], $questionStatistics)) {
                        /** @var Item $itemEntity */
                        $itemEntity = $itemRepository->findOneBy(['uuid' => $item['id']]);
                        $questionStats = $this->itemManager->getStatistics($itemEntity, $exercise);
                        $questionStatistics[$item['id']] = [
                            'yData' => $questionStats['successPercent'],
                            'xData' => $itemEntity->getTitle() ? strip_tags($itemEntity->getTitle()) : strip_tags($itemEntity->getContent()),
                        ];
                    }
                }
            }
        }

        return $questionStatistics;
    }

    public function getAttemptsScores(Exercise $exercise, bool $finishedOnly = false, User $user = null)
    {
        $data = [
            'total' => [],
        ];

        $totalScores = $this->paperRepository->getAvgScoreByAttempts($exercise, $finishedOnly, $user);
        foreach ($totalScores as $totalScore) {
            // create associative array because D3 charts expect objects
            $data['total']['attempt'.$totalScore['number']] = [
                'xData' => $totalScore['number'],
                'yData' => (float) $totalScore['score'],
            ];
        }

        $scoreOn = null;
        $exerciseScore = $exercise->getScoreRule() ? json_decode($exercise->getScoreRule(), true) : null;
        if (!empty($exerciseScore) && !empty($exerciseScore['total'])) {
            $scoreOn = $exerciseScore['total'];
        }

        $itemsScores = $this->answerRepository->getAvgScoreByAttempts($exercise, $finishedOnly, $user);
        foreach ($itemsScores as $itemsScore) {
            $question = $exercise->getQuestion($itemsScore['questionId']);
            if (empty($question)) {
                // do not show deleted questions
                continue;
            }

            if (!isset($data[$itemsScore['questionId']])) {
                $data[$itemsScore['questionId']] = [];
            }

            $score = $itemsScore['score'];
            if (!empty($scoreOn)) {
                $score = ($itemsScore['score'] / $this->itemManager->calculateTotal($question)) * $scoreOn;
            }

            $data[$itemsScore['questionId']]['attempt'.$itemsScore['number']] = [
                'xData' => $itemsScore['number'],
                'yData' => $score,
            ];
        }

        return $data;
    }

    /**
     * Get discrimination coefficient for an exercise.
     *
     * @return array
     */
    private function getDiscriminationCoefficient(Exercise $exercise)
    {
        // get all scores for the exercise
        $papers = $this->paperRepository->findBy([
            'exercise' => $exercise,
        ]);

        $itemRepository = $this->om->getRepository(Item::class);
        $reportScoreOn = 100;
        // all scores obtained for an exercise
        $exerciseScores = $this->getPapersScores($papers, $reportScoreOn);
        // exercise standard deviation
        $standardDeviationE = $this->getStandardDeviation($exerciseScores);
        // get average score per exercise
        $exerciseAverageScore = $this->getMinMaxAverageScores($exercise, $reportScoreOn)['avg'];
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
            // base success computation on paper structure
            $structure = $paper->getStructure(true);

            foreach ($structure['steps'] as $step) {
                foreach (array_filter($step['items'], function ($item) {
                    return ItemType::isSupported($item['type']);
                }) as $item) {
                    // since the computation is based on the structure the same item can come several times
                    if (!array_key_exists($item['id'], $discriminationCoef)) {
                        $itemEntity = $itemRepository->findOneBy(['uuid' => $item['id']]);
                        // set questions scores
                        $questionsScores[$item['id']] = $this->itemManager->getItemScores($exercise, $itemEntity);
                        // get average score for the item
                        $questionsAvgScores[$item['id']] = array_sum($questionsScores[$item['id']]) / count($questionsScores[$item['id']]);
                        $i = 0;

                        foreach ($questionsScores[$item['id']] as $score) {
                            $questionsMarginMark[$item['id']][] = ($score - $questionsAvgScores[$item['id']]) * ($exerciseScores[$i] - $exerciseAverageScore);
                            ++$i;
                        }

                        $questionProductMarginMark = $questionsMarginMark[$item['id']];
                        $sumPenq = array_sum($questionProductMarginMark);
                        $sumPenq = round($sumPenq, 3);
                        $standardDeviationQ = $this->getStandardDeviation($questionsScores[$item['id']]);
                        $n = count($questionProductMarginMark);
                        $nSxSy = $n * $standardDeviationQ * $standardDeviationE;
                        $coef = $nSxSy === floatval(0) ? 0 : round($sumPenq / ($nSxSy), 3);

                        $discriminationCoef[$item['id']] = [
                            'xData' => $itemEntity->getTitle() ? strip_tags($itemEntity->getTitle()) : strip_tags($itemEntity->getContent()),
                            'yData' => $coef,
                        ];
                    }
                }
            }
        }

        return $discriminationCoef;
    }

    /**
     * Get standard deviation for the discrimination coefficient.
     *
     * @param array $array
     *
     * @return float
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
     * @param array $papers
     * @param float $scoreOn
     *
     * @return array
     */
    private function getPapersScores($papers, $scoreOn = null)
    {
        $scores = [];
        /** @var Paper $paper */
        foreach ($papers as $paper) {
            if ($paper->getTotal()) {
                $score = $this->paperManager->calculateScore($paper);
                // since totalScoreOn might have change through papers report all scores on a define value
                if ($scoreOn) {
                    $score = floatval(($scoreOn * $score) / $paper->getTotal());
                }

                $scores[] = $score !== floor($score) ? floatval(number_format($score, 2)) : $score;
            }
        }

        return $scores;
    }
}
