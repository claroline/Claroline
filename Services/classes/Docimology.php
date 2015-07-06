<?php

/**
 *
 * Services for the docimology
 */

namespace UJM\ExoBundle\Services\classes;

use Doctrine\Bundle\DoctrineBundle\Registry;
use \Symfony\Component\DependencyInjection\Container;

class Docimology {

    private $doctrine;
    private $container;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection;
     * @param \Symfony\Component\DependencyInjection\Container $container
     *
     */
    public function __construct(Registry $doctrine, Container $container)
    {
        $this->doctrine  = $doctrine;
        $this->container = $container;
    }

    /**
     * Docimology
     * To draw histogram of success
     *
     * @access public
     *
     * @param integer $exerciseId
     * @param doctrine collection $eqs questions linked with the exercise
     * @param doctrine collection $papers papers linked with the exercise
     *
     * @return array
     */
    public function histoSuccess($exerciseId, $eqs, $papers)
    {
        $em = $this->doctrine->getManager();
        $questionsResponsesTab = array();
        $seriesResponsesTab = array();
        $seriesResponsesTab[0] = '';
        $seriesResponsesTab[1] = '';
        $seriesResponsesTab[2] = '';
        $seriesResponsesTab[3] = '';
        $questionList = array();
        $histoSuccess = array();
        $maxY = 4;

        foreach ($eqs as $eq) {
            $questionList[] = $eq->getQuestion()->getTitle();

            $responsesTab = $this->getCorrectAnswer($exerciseId, $eq, $em);

            $questionsResponsesTab[$eq->getQuestion()->getId()] = $responsesTab;

        }

        //no response
        foreach ($papers as $paper) {
            $interQuestions = $paper->getOrdreQuestion();
            $interQuestions = substr($interQuestions, 0, strlen($interQuestions) - 1);

            $interQuestionsTab = explode(";", $interQuestions);
            foreach ($interQuestionsTab as $interQuestion) {
                $flag = $em->getRepository('UJMExoBundle:Response')->findOneBy(
                    array(
                        'interaction' => $interQuestion,
                        'paper' => $paper->getId()
                    )
                );

                if (!$flag || $flag->getResponse() == '') {
                    $interaction = $em->getRepository('UJMExoBundle:Interaction')->find($interQuestion);
                    $questionsResponsesTab[$interaction->getQuestion()->getId()]['noResponse'] += 1;
                }
            }
        }

        //creation serie for the graph jqplot
        foreach ($questionsResponsesTab as $responses) {
            $tot = (int) $responses['correct'] + (int) $responses['partiallyRight'] + (int) $responses['wrong'] + (int) $responses['noResponse'];
            if ($tot > $maxY ) {
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
     * To draw histogram of discrimination
     *
     * @access public
     *
     * @param integer $exerciseId
     * @param doctrine collection $eqs questions linked with the exercise
     * @param doctrine collection $papers papers linked with the exercise
     *
     * @return array
     */
    public function histoDiscrimination($exerciseId, $eqs, $papers)
    {
        $em = $this->doctrine->getManager();
        $tabScoreExo = array();
        $tabScoreQ = array();
        $tabScoreAverageQ = array();
        $productMarginMark = array();
        $tabCoeffQ = array();
        $histoDiscrimination = array();
        $scoreAverageExo = 0;
        $marks = $em->getRepository('UJMExoBundle:Response')->getExerciseMarks($exerciseId, 'paper');

        //Array of exercise's scores
        foreach ($marks as $mark) {
            $tabScoreExo[] = $mark["noteExo"];
        }

        //Average exercise's score
        foreach ($tabScoreExo as $se) {
            $scoreAverageExo += (float) $se;
        }

        $scoreAverageExo = $scoreAverageExo / count($tabScoreExo);

        //Array of each question's score
        foreach ($eqs as $eq) {
            $interaction = $em->getRepository('UJMExoBundle:Interaction')->getInteraction($eq->getQuestion()->getId());
            $responses = $em->getRepository('UJMExoBundle:Response')
                            ->getExerciseInterResponses($exerciseId, $interaction->getId());
            foreach ($responses as $response) {
                $tabScoreQ[$eq->getQuestion()->getId()][] = $response['mark'];
            }

            while ((count($tabScoreQ[$eq->getQuestion()->getId()])) < (count($papers))) {
                $tabScoreQ[$eq->getQuestion()->getId()][] = 0;
            }
        }

        //Array of average of each question's score
        foreach ($eqs as $eq) {
            $allScoreQ = $tabScoreQ[$eq->getQuestion()->getId()];
            $sm = 0;
            foreach ($allScoreQ as $sq) {
                $sm += $sq;
            }
            $sm = $sm / count($papers);
            $tabScoreAverageQ[$eq->getQuestion()->getId()] = $sm;
        }

        //Array of (x-Mx)(y-My)
        foreach ($eqs as $eq) {
            $i = 0;
            $allScoreQ = $tabScoreQ[$eq->getQuestion()->getId()];
            foreach ($allScoreQ as $sq) {
                $productMarginMark[$eq->getQuestion()->getId()][] = ($sq - $tabScoreAverageQ[$eq->getQuestion()->getId()]) * ($tabScoreExo[$i] - $scoreAverageExo);
                $i++;
            }
        }

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

        $coeffQ = implode(",", $tabCoeffQ);
        $histoDiscrimination['coeffQ'] = $coeffQ;

        return $histoDiscrimination;
    }

    /**
     * Docimology
     * To draw histogram of marks
     *
     * @access public
     *
     * @param integer $exerciseId
     *
     * @return array
     */
    public function histoMark($exerciseId)
    {
        $paperSer = $this->container->get('ujm.exo_paper');
        $em = $this->doctrine->getManager();
        $maxY = 4;
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exerciseId);
        if ($exercise->getNbQuestion() == 0) {
            $exoScoreMax = $this->container->get('ujm.exo_exercise')->getExerciseTotalScore($exerciseId);
        }
        $marks = $em->getRepository('UJMExoBundle:Response')->getExerciseMarks($exerciseId, 'noteExo');
        $tabMarks = array();
        $histoMark = array();

        foreach ($marks as $mark) {
            if ($exercise->getNbQuestion() > 0) {
                $exoScoreMax = $this->container->get('ujm.exo_paper')->getPaperTotalScore($mark['paper']);
            }
            $scoreU = round(($mark["noteExo"] / $exoScoreMax) * 20, 2);

            $score = $paperSer->roundUpDown($scoreU);

            if (isset($tabMarks[(string) $score])) {
                $tabMarks[(string) $score] += 1;
            } else {
                $tabMarks[(string) $score] = 1;
            }
        }

        ksort($tabMarks);
        $scoreList = implode(",", array_keys($tabMarks));

        if (max($tabMarks) > 4) {
            $maxY = max($tabMarks);
        }

        $frequencyMarks = implode(",", $tabMarks);

        $histoMark['maxY']           = $maxY;
        $histoMark['scoreList']      = $scoreList;
        $histoMark['frequencyMarks'] = $frequencyMarks;

        return $histoMark;
    }

    /**
     * Docimology
     * To draw histogram of difficulty
     *
     * @access public
     *
     * @param integer $exerciseId
     * @param doctrine collection $eqs questions linked with the exercise
     *
     * @return string
     */
    public function histoMeasureOfDifficulty($exerciseId, $eqs)
    {
        $em = $this->doctrine->getManager();
        $paperSer = $this->container->get('ujm.exo_paper');
        $up = array();
        $down = array();
        $measureTab = array();

        foreach ($eqs as $eq) {

            $responsesTab = $this->getCorrectAnswer($exerciseId, $eq, $em);

            $up[] = $responsesTab['correct'];
            $down[] = (int) $responsesTab['correct'] + (int) $responsesTab['partiallyRight'] + (int) $responsesTab['wrong'];
        }

        $stop = count($up);

        for ($i = 0; $i < $stop; $i++) {

            $measureTab[$i] = $paperSer->roundUpDown(($up[$i] / $down[$i]) * 100);
        }

        $measure = implode(",", $measureTab);

        return $measure;
    }

    /**
     * Docimology
     * To have the status of an answer
     *
     * @access private
     *
     * @param array $responses result of getExerciseInterResponsesWithCount (ResponseRepository)
     * @param float $scoreMax score max possible for a question
     *
     * @return array
     */
    private function responseStatus($responses, $scoreMax)
    {
        $responsesTab = array();
        $responsesTab['correct']        = 0;
        $responsesTab['partiallyRight'] = 0;
        $responsesTab['wrong']          = 0;
        $responsesTab['noResponse']     = 0;

        foreach ($responses as $rep) {
            if ($rep['mark'] == $scoreMax) {
                $responsesTab['correct'] = $rep['nb'];
            } else if ($rep['mark'] == 0) {
                $responsesTab['wrong'] = $rep['nb'];
            } else {
                $responsesTab['partiallyRight'] += $rep['nb'];
            }
        }

        return $responsesTab;
    }

    /**
     * Docimology, to calulate the standard deviation for the discrimination coefficient
     *
     * @param type $x
     * @param type $mean
     * @return type
     */
    private function sd_square($x, $mean)
    {
        return pow($x - $mean, 2);

    }

    /**
     *
     * Docimology, to calulate the standard deviation for the discrimination coefficient
     *
     * @param type $array
     * @return type
     */
    private function sd($array)
    {

        return sqrt(array_sum(array_map(array($this, "sd_square"), $array, array_fill(0, count($array), (array_sum($array) / count($array))))) / (count($array) - 1));
    }

    /**
     * Docimology
     * To get the number of answers with the 'correct' status
     *
     * @access private
     *
     * @param integer $exerciseId
     * @param doctrine collection $eqs questions linked with the exercise
     * @param Doctrine Entity manager $em
     *
     * @return array
     */
    private function getCorrectAnswer($exerciseId, $eq, $em)
    {
        $em = $this->doctrine->getManager();

        $interaction = $em->getRepository('UJMExoBundle:Interaction')->getInteraction($eq->getQuestion()->getId());

        $responses = $em->getRepository('UJMExoBundle:Response')
                        ->getExerciseInterResponsesWithCount($exerciseId, $interaction->getId());
        $typeInter = $interaction->getType();
        $interSer  = $this->container->get('ujm.exo_' . $typeInter);
        $interX = $interSer->getInteractionX($interaction->getId());
        $scoreMax = $interSer->maxScore($interX);
        $responsesTab = $this->responseStatus($responses, $scoreMax);

        return $responsesTab;
    }

}
