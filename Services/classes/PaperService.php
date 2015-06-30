<?php

/**
 *
 * Services for the paper
 */

namespace UJM\ExoBundle\Services\classes;

use Doctrine\Bundle\DoctrineBundle\Registry;

class PaperService {

    private $doctrine;


    /**
     * Constructor
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection;
     *
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Get IP client
     *
     * @access public
     * @param Request $request
     *
     * @return IP Client
     */
    public function getIP(Request $request)
    {

        return $request->getClientIp();
    }

    /**
     * Get total score for an paper
     *
     * @access public
     *
     * @param integer $paperID id Paper
     *
     * @return float
     */
    public function getPaperTotalScore($paperID)
    {
        $em = $this->doctrine->getManager();
        $exercisePaperTotalScore = 0;
        $paper = $interaction = $em->getRepository('UJMExoBundle:Paper')
                                   ->find($paperID);

        $interQuestions = $paper->getOrdreQuestion();
        $interQuestions = substr($interQuestions, 0, strlen($interQuestions) - 1);
        $interQuestionsTab = explode(";", $interQuestions);

        foreach ($interQuestionsTab as $interQuestion) {
            $interaction = $em->getRepository('UJMExoBundle:Interaction')->find($interQuestion);
            $interSer        = $this->container->get('ujm.exo_' . $interaction->getType());
            $interactionX    = $interSer->getInteractionX($interaction->getId());
            $exercisePaperTotalScore += $interSer->maxScore($interactionX);
        }

        return $exercisePaperTotalScore;
    }

    /**
     * To round up and down a score
     *
     * @access public
     *
     * @param float $toBeAdjusted
     *
     * @return float
     */
    public function roundUpDown($toBeAdjusted)
    {
        return (round($toBeAdjusted / 0.5) * 0.5);
    }

    /**
     * Get informations about a paper response, maxExoScore, scorePaper, scoreTemp (all questions graphiced or no)
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\paper $paper
     *
     * @return array
     */
    public function getInfosPaper($paper)
    {
        $infosPaper = array();
        $scorePaper = 0;
        $scoreTemp = false;

        $interactions = $this->getInteractions($paper->getOrdreQuestion());
        $interactionsSorted = $this->sortInteractions($interactions, $paper->getOrdreQuestion());
        $infosPaper['interactions'] = $interactionsSorted;

        $responses = $this->getResponses($paper->getUser()->getId(), $paper->getId());
        $responsesSorted = $this->sortResponses($responses, $paper->getOrdreQuestion());
        $infosPaper['responses'] = $responsesSorted;

        $infosPaper['maxExoScore'] = $this->getPaperTotalScore($paper->getId());

        foreach ($responses as $response) {
            if ($response->getMark() != -1) {
                $scorePaper += $response->getMark();
            } else {
                $scoreTemp = true;
            }
        }

        $infosPaper['scorePaper'] = $scorePaper;
        $infosPaper['scoreTemp'] = $scoreTemp;

        return $infosPaper;
    }

    /**
     * sort the array of interactions in the order recorded for the paper
     *
     * @access private
     *
     * @param Collection of \UJM\ExoBundle\Entity\Interaction $interactions
     * @param String $order
     *
     * @return UJM\ExoBundle\Entity\Interaction[]
     */
    private function sortInteractions($interactions, $order)
    {
        $inter = array();
        $order = substr($order, 0, strlen($order) - 1);
        $order = explode(';', $order);

        foreach ($order as $interId) {
            foreach ($interactions as $key => $interaction) {
                if ($interaction->getId() == $interId) {
                    $inter[] = $interaction;
                    unset($interactions[$key]);
                    break;
                }
            }
        }

        return $inter;
    }

    /**
     * sort the array of responses to match the order of questions
     *
     * @access private
     *
     * @param Collection of \UJM\ExoBundle\Entity\Response $responses
     * @param String $order
     *
     * @return UJM\ExoBundle\Entity\Response[]
     */
    private function sortResponses($responses, $order)
    {
        $resp = array();
        $order = $this->formatQuestionOrder($order);
        foreach ($order as $interId) {
            $tem = 0;
            foreach ($responses as $key => $response) {
                if ($response->getInteraction()->getId() == $interId) {
                    $tem++;
                    $resp[] = $response;
                    unset($responses[$key]);
                    break;
                }
            }
            //if no response
            if ($tem == 0) {
                $response = new \UJM\ExoBundle\Entity\Response();
                $response->setResponse('');
                $response->setMark(0);

                $resp[] = $response;
            }
        }

        return $resp;
    }

    /**
     *
     * @access private
     *
     * @param String $order
     *
     * Return \UJM\ExoBundle\Interaction[]
     */
    private function getInteractions($orderQuestion)
    {
        $em = $this->doctrine->getManager();

        $interactions = $em->getRepository('UJMExoBundle:Interaction')
                           ->getPaperInteraction($em, str_replace(';', '\',\'', substr($orderQuestion, 0, -1)));

        return $interactions;
    }

    /**
     *
     * @access private
     *
     * @param integer $userId
     * @param integer $paperId
     *
     * Return \UJM\ExoBundle\Entity\Interaction[]
     */
    private function getResponses($userId, $paperId)
    {
        $em = $this->doctrine->getManager();

        $responses = $em->getRepository('UJMExoBundle:Response')
                        ->getPaperResponses($userId, $paperId);

        return $responses;
    }

    /**
     *
     * @access private
     *
     * @param String $order
     *
     * Return integer[];
     */
    private function formatQuestionOrder($orderOrig)
    {
        $order = substr($orderOrig, 0, strlen($orderOrig) - 1);
        $orderFormated = explode(';', $order);

        return $orderFormated;
    }
}
