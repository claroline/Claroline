<?php

/**
 * Services for the docimology.
 */
namespace UJM\ExoBundle\Services\classes;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\Container;

class Docimology
{
    private $doctrine;
    private $container;

    /**
     * Constructor.
     *
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry         $doctrine  Dependency Injection;
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(Registry $doctrine, Container $container)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
    }

    /**
     * Docimology
     * To draw histogram of success.
     *
     *
     * @param int                 $exerciseId
     * @param doctrine collection $eqs        questions linked with the exercise
     * @param doctrine collection $papers     papers linked with the exercise
     *
     * @return array
     */
    public function histoSuccess($exerciseId, $eqs, $papers)
    {
        $em = $this->doctrine->getManager();
        $seriesResponsesTab = array();
        $seriesResponsesTab[0] = '';
        $seriesResponsesTab[1] = '';
        $seriesResponsesTab[2] = '';
        $seriesResponsesTab[3] = '';
        $histoSuccess = array();
        $maxY = 4;

        $questionList = $this->getQuestionsTitle($eqs);
        $questionsResponsesTab = $this->getQuestionsCorrectAnswer($eqs, $exerciseId);

        //no response
        foreach ($papers as $paper) {
            $interQuestions = $paper->getOrdreQuestion();
            $interQuestions = substr($interQuestions, 0, strlen($interQuestions) - 1);

            $interQuestionsTab = explode(';', $interQuestions);
            foreach ($interQuestionsTab as $interQuestion) {
                $flag = $em->getRepository('UJMExoBundle:Response')->findOneBy(
                    array(
                        'question' => $interQuestion,
                        'paper' => $paper->getId(),
                    )
                );

                if (!$flag || $flag->getResponse() == '') {
                    $questionsResponsesTab[$interQuestion]['noResponse'] += 1;
                }
            }
        }

        //creation serie for the graph jqplot
        foreach ($questionsResponsesTab as $responses) {
            $tot = (int) $responses['correct'] + (int) $responses['partiallyRight'] + (int) $responses['wrong'] + (int) $responses['noResponse'];
            if ($tot > $maxY) {
                $maxY = $tot;
            }
            $seriesResponsesTab[0] .= (string) $responses['correct'].',';
            $seriesResponsesTab[1] .= (string) $responses['partiallyRight'].',';
            $seriesResponsesTab[2] .= (string) $responses['wrong'].',';
            $seriesResponsesTab[3] .= (string) $responses['noResponse'].',';
        }

        foreach ($seriesResponsesTab as $s) {
            $s = substr($s, 0, strlen($s) - 1);
        }

        $histoSuccess['questionsList'] = $questionList;
        $histoSuccess['seriesResponsesTab'] = $seriesResponsesTab;
        $histoSuccess['maxY'] = $maxY;

        return $histoSuccess;
    }

    /**
     * Docimology
     * To draw histogram of discrimination.
     *
     *
     * @param int                 $exerciseId
     * @param doctrine collection $eqs        questions linked with the exercise
     * @param doctrine collection $papers     papers linked with the exercise
     *
     * @return float[]
     */
    public function histoDiscrimination($exerciseId, $eqs, $papers)
    {
        $histoDiscrimination = array();

        $tabScoreExo = $this->getScoreByExercise($exerciseId);
        $scoreAverageExo = $this->getExerciseScoreAverage($tabScoreExo);
        $tabScoreQ = $this->getScoreByQuestion($exerciseId, $eqs, $papers);
        $tabScoreAverageQ = $this->getQuestionsScoreAverage($eqs, $tabScoreQ, count($papers));
        $productMarginMark = $this->getProductsMarginMark($eqs, $tabScoreQ, $tabScoreAverageQ, $tabScoreExo, $scoreAverageExo);
        $tabCoeffQ = $this->getCoeffOfDiscrimination($eqs, $productMarginMark, $tabScoreExo, $tabScoreQ);

        $coeffQ = implode(',', $tabCoeffQ);
        $histoDiscrimination['coeffQ'] = $coeffQ;

        return $histoDiscrimination;
    }

    /**
     * Docimology
     * To draw histogram of marks.
     *
     *
     * @param int $exerciseId
     *
     * @return array
     */
    public function histoMark($exerciseId)
    {
        $em = $this->doctrine->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exerciseId);
        $maxY = 4;
        $tabMarks = $this->getArrayMarks($exercise);
        $histoMark = array();

        $scoreList = implode(',', array_keys($tabMarks));

        if (max($tabMarks) > 4) {
            $maxY = max($tabMarks);
        }

        $frequencyMarks = implode(',', $tabMarks);

        $histoMark['maxY'] = $maxY;
        $histoMark['scoreList'] = $scoreList;
        $histoMark['frequencyMarks'] = $frequencyMarks;

        return $histoMark;
    }

    /**
     * Docimology
     * To draw histogram of difficulty.
     *
     *
     * @param int                 $exerciseId
     * @param doctrine collection $eqs        questions linked with the exercise
     *
     * @return string
     */
    public function histoMeasureOfDifficulty($exerciseId, $eqs)
    {
        $paperSer = $this->container->get('ujm.exo_paper');
        $up = array();
        $down = array();
        $measureTab = array();

        foreach ($eqs as $eq) {
            $responsesTab = $this->getCorrectAnswer($exerciseId, $eq);

            $up[] = $responsesTab['correct'];
            $down[] = (int) $responsesTab['correct'] + (int) $responsesTab['partiallyRight'] + (int) $responsesTab['wrong'];
        }

        $stop = count($up);

        for ($i = 0; $i < $stop; ++$i) {
            $measureTab[$i] = $paperSer->roundUpDown(($up[$i] / $down[$i]) * 100);
        }

        $measure = implode(',', $measureTab);

        return $measure;
    }

    /**
     * Docimology
     * To have the status of an answer.
     *
     *
     * @param array $responses result of getExerciseInterResponsesWithCount (ResponseRepository)
     * @param float $scoreMax  score max possible for a question
     *
     * @return array
     */
    private function responseStatus($responses, $scoreMax)
    {
        $responsesTab = array();
        $responsesTab['correct'] = 0;
        $responsesTab['partiallyRight'] = 0;
        $responsesTab['wrong'] = 0;
        $responsesTab['noResponse'] = 0;

        foreach ($responses as $rep) {
            if ($rep['mark'] == $scoreMax) {
                $responsesTab['correct'] = $rep['nb'];
            } elseif ($rep['mark'] == 0) {
                $responsesTab['wrong'] = $rep['nb'];
            } else {
                $responsesTab['partiallyRight'] += $rep['nb'];
            }
        }

        return $responsesTab;
    }

    /**
     * Docimology, to calulate the standard deviation for the discrimination coefficient.
     *
     * @param type $x
     * @param type $mean
     *
     * @return type
     */
    private function sd_square($x, $mean)
    {
        return pow($x - $mean, 2);
    }

    /**
     * Docimology, to calulate the standard deviation for the discrimination coefficient.
     *
     * @param type $array
     *
     * @return type
     */
    private function sd($array)
    {
        return sqrt(array_sum(array_map(array($this, 'sd_square'), $array, array_fill(0, count($array), (array_sum($array) / count($array))))) / (count($array) - 1));
    }

    /**
     * Docimology
     * To get the number of answers with the 'correct' status.
     *
     *
     * @param int                 $exerciseId
     * @param doctrine collection $eqs        questions linked with the exercise
     *
     * @return array
     */
    private function getCorrectAnswer($exerciseId, $eq)
    {
        $em = $this->doctrine->getManager();
        $question = $eq->getQuestion();
        $responses = $em->getRepository('UJMExoBundle:Response')
                        ->getExerciseInterResponsesWithCount($exerciseId, $question->getId());
        $typeInter = $question->getType();
        $interSer = $this->container->get('ujm.exo_'.$typeInter);
        $interX = $interSer->getInteractionX($question->getId());
        $scoreMax = $interSer->maxScore($interX);
        $responsesTab = $this->responseStatus($responses, $scoreMax);

        return $responsesTab;
    }

    /**
     * Docimology
     * To get array of marks for an exercise.
     *
     *
     * @param UJM\ExoBundle\Entity\Exercise $exercise
     *
     * @return float[]
     */
    private function getArrayMarks($exercise)
    {
        $paperSer = $this->container->get('ujm.exo_paper');
        $em = $this->doctrine->getManager();
        if ($exercise->getNbQuestion() == 0) {
            $exoScoreMax = $this->container->get('ujm.exo_exercise')->getExerciseTotalScore($exercise->getId());
        }
        $marks = $em->getRepository('UJMExoBundle:Response')->getExerciseMarks($exercise->getId(), 'noteExo');
        foreach ($marks as $mark) {
            if ($exercise->getNbQuestion() > 0) {
                $exoScoreMax = $this->container->get('ujm.exo_paper')->getPaperTotalScore($mark['paper']);
            }
            $scoreU = round(($mark['noteExo'] / $exoScoreMax) * 20, 2);

            $score = $paperSer->roundUpDown($scoreU);

            if (isset($tabMarks[(string) $score])) {
                $tabMarks[(string) $score] += 1;
            } else {
                $tabMarks[(string) $score] = 1;
            }
        }

        ksort($tabMarks);

        return $tabMarks;
    }

    /**
     * @param int                        $exerciseId
     * @param doctrine collection        $eqs        questions linked with the exercise
     * @param UJM\ExoBundle\Entity\Paper $papers     papers linked with the exercise
     *
     * @return float[][] Array of each question's score
     */
    private function getScoreByQuestion($exerciseId, $eqs, $papers)
    {
        $em = $this->doctrine->getManager();
        $tabScoreQ = array();
        foreach ($eqs as $eq) {
            $responses = $em->getRepository('UJMExoBundle:Response')
                            ->getExerciseInterResponses($exerciseId, $eq->getQuestion()->getId());
            foreach ($responses as $response) {
                $tabScoreQ[$eq->getQuestion()->getId()][] = $response['mark'];
            }

            while ((count($tabScoreQ[$eq->getQuestion()->getId()])) < (count($papers))) {
                $tabScoreQ[$eq->getQuestion()->getId()][] = 0;
            }
        }

        return $tabScoreQ;
    }

    /**
     * @param int $exerciseId
     *
     * @return float[] Array of score for an exercise
     */
    private function getScoreByExercise($exerciseId)
    {
        $em = $this->doctrine->getManager();
        $marks = $em->getRepository('UJMExoBundle:Response')->getExerciseMarks($exerciseId, 'paper');
        $tabScoreExo = array();

        foreach ($marks as $mark) {
            $tabScoreExo[] = $mark['noteExo'];
        }

        return $tabScoreExo;
    }

    /**
     * @param float[] $tabScoreExo
     *
     * @return float score average for an exercise
     */
    private function getExerciseScoreAverage($tabScoreExo)
    {
        $scoreAverageExo = 0;
        //Average exercise's score
        foreach ($tabScoreExo as $se) {
            $scoreAverageExo += (float) $se;
        }

        $scoreAverageExo = $scoreAverageExo / count($tabScoreExo);

        return $scoreAverageExo;
    }

    /**
     * @param doctrine collection $eqs         questions linked with the exercise
     * @param float[]             $tabScoreExo
     * @param int                 $nbPapers
     *
     * @return float[] Array of average of each question's score
     */
    private function getQuestionsScoreAverage($eqs, $tabScoreQ, $nbPapers)
    {
        $tabScoreAverageQ = array();
        foreach ($eqs as $eq) {
            $allScoreQ = $tabScoreQ[$eq->getQuestion()->getId()];
            $sm = 0;
            foreach ($allScoreQ as $sq) {
                $sm += $sq;
            }
            $sm = $sm / $nbPapers;
            $tabScoreAverageQ[$eq->getQuestion()->getId()] = $sm;
        }

        return $tabScoreAverageQ;
    }

    /**
     * @param doctrine collection $eqs              questions linked with the exercise
     * @param float[][]           $tabScoreQ        Array of each question's score
     * @param float[]             $tabScoreAverageQ Array of average of each question's score
     * @param float[]             $tabScoreExo      Array of score for an exercise
     * @param float               $scoreAverageExo  score average for an exercise
     *
     * @return float[][] Array of (x-Mx)(y-My) by questions
     */
    private function getProductsMarginMark($eqs, $tabScoreQ, $tabScoreAverageQ, $tabScoreExo, $scoreAverageExo)
    {
        $productMarginMark = array();
        foreach ($eqs as $eq) {
            $i = 0;
            $allScoreQ = $tabScoreQ[$eq->getQuestion()->getId()];
            foreach ($allScoreQ as $sq) {
                $productMarginMark[$eq->getQuestion()->getId()][] = ($sq - $tabScoreAverageQ[$eq->getQuestion()->getId()]) * ($tabScoreExo[$i] - $scoreAverageExo);
                ++$i;
            }
        }

        return $productMarginMark;
    }

    /**
     * @param doctrine collection                          $eqs               questions linked with the exercise
     * @param float[][] Array of (x-Mx)(y-My) by questions $productMarginMark
     * @param float[]                                      $tabScoreExo       Array of score for an exercise
     * @param float[][]                                    $tabScoreQ         Array of each question's score
     *
     * @return float[] array of coefficient of discrimination for each questions
     */
    private function getCoeffOfDiscrimination($eqs, $productMarginMark, $tabScoreExo, $tabScoreQ)
    {
        $tabCoeffQ = array();
        foreach ($eqs as $eq) {
            $productMarginMarkQ = $productMarginMark[$eq->getQuestion()->getId()];
            $sumPenq = 0;
            $standardDeviationQ = null;
            $standardDeviationE = $this->sd($tabScoreExo);
            $n = count($productMarginMarkQ);
            foreach ($productMarginMarkQ as $penq) {
                $sumPenq += $penq;
            }
            $sumPenq = round($sumPenq, 3);
            $standardDeviationQ = $this->sd($tabScoreQ[$eq->getQuestion()->getId()]);
            $nSxSy = $n * $standardDeviationQ * $standardDeviationE;
            if ($nSxSy != 0) {
                $tabCoeffQ[] = round($sumPenq / ($nSxSy), 3);
            } else {
                $tabCoeffQ[] = 0;
            }
        }

        return $tabCoeffQ;
    }

    /**
     * @param doctrine collection $eqs questions linked with the exercise
     *
     * @return String[] array of title of each question of an exercise
     */
    private function getQuestionsTitle($eqs)
    {
        $questionList = array();
        foreach ($eqs as $eq) {
            $questionList[] = $eq->getQuestion()->getTitle();
        }

        return $questionList;
    }

    /**
     * @param doctrine collection $eqs        questions linked with the exercise
     * @param int                 $exerciseId
     *
     * @return mixed[] array correcte response for each questions for an exercise
     */
    private function getQuestionsCorrectAnswer($eqs, $exerciseId)
    {
        $questionsResponsesTab = array();
        foreach ($eqs as $eq) {
            $responsesTab = $this->getCorrectAnswer($exerciseId, $eq);
            $questionsResponsesTab[$eq->getQuestion()->getId()] = $responsesTab;
        }

        return $questionsResponsesTab;
    }
}
