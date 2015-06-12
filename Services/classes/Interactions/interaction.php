<?php

/**
 * abstract class
 *
 */

namespace UJM\ExoBundle\Services\classes\Interactions;

use Doctrine\Bundle\DoctrineBundle\Registry;


abstract class interaction {

    protected $doctrine;
    protected $om;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Claroline\CoreBundle\Persistence\ObjectManager $om Dependency Injection
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection;
     *
     */
    public function __construct(
        \Claroline\CoreBundle\Persistence\ObjectManager $om, Registry $doctrine
    )
    {
        $this->doctrine = $doctrine;
        $this->om       = $om;
    }

    /**
     * Get penalty for an interaction and a paper
     *
     * @access private
     *
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     * @param integer $paperID id Paper
     *
     * @return array
     */
    private function getPenaltyPaper($interaction, $paperID)
    {
        $penalty = 0;

        $hints = $interaction->getHints();

        foreach ($hints as $hint) {
            $lhp = $this->om
                        ->getRepository('UJMExoBundle:LinkHintPaper')
                        ->getLHP($hint->getId(), $paperID);
            if (count($lhp) > 0) {
                $signe = substr($hint->getPenalty(), 0, 1);

                if ($signe == '-') {
                    $penalty += substr($hint->getPenalty(), 1);
                } else {
                    $penalty += $hint->getPenalty();
                }
            }
        }

        return $penalty;
    }

    /**
     * Get penalty for a test or a paper
     *
     * @access protected
     *
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param int $paperID
     *
     * @return int
     */
    protected function getPenalty($interaction, \Symfony\Component\HttpFoundation\Session\SessionInterface $session, $paperID)
    {
        $penalty = 0;
        if ($paperID == 0) {
            if ($session->get('penalties')) {
                foreach ($session->get('penalties') as $penal) {

                    $signe = substr($penal, 0, 1); // In order to manage the symbol of the penalty

                    if ($signe == '-') {
                        $penalty += substr($penal, 1);
                    } else {
                        $penalty += $penal;
                    }
                }
            }
            $session->remove('penalties');
        } else {
            $penalty = $this->getPenaltyPaper($interaction, $paperID);
        }

        return $penalty;
    }

    /**
     * Get score for a question with key word
     *
     * @access protected
     *
     * @param \UJM\ExoBundle\Entity\WordResponse $wr
     * @param String $response
     *
     * @return float
     */
     protected function getScoreWordResponse($wr, $response)
     {
         $score = 0;
         if ( ((strcasecmp(trim($wr->getResponse()), trim($response)) == 0
                 && $wr->getCaseSensitive() == false))
                     || (trim($wr->getResponse()) == trim($response)) ) {
             $score = $wr->getScore();
         }

         return $score;
     }

    /**
      *
      * Find if exist already an answer
      *
      * @access protected
      * @param \UJM\ExoBundle\Entity\Interaction $interactionToDisplay interaction (question) to displayed
      * @param Symfony\Component\HttpFoundation\Session\SessionInterface $session
      *
      * @return \UJM\ExoBundle\Entity\Response
      */
     public function getAlreadyResponded($interactionToDisplay, $session)
     {
         $responseGiven = $this->doctrine
                               ->getManager()
                               ->getRepository('UJMExoBundle:Response')
                               ->getAlreadyResponded($session->get('paper'), $interactionToDisplay->getId());

         if (count($responseGiven) > 0) {
             $responseGiven = $responseGiven[0]->getResponse();
         } else {
             $responseGiven = '';
         }

         return $responseGiven;
     }

    /**
     * abstract method
     * To process the user's response for a paper(or a test)
     *
     * @access public
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * @return array
     */
     abstract public function response(\Symfony\Component\HttpFoundation\Request $request, $paperID = 0);

     /**
      * abstract method
      * To calculate the score for a question
      *
      * @access public
      *
      * @return string userScore/scoreMax
      */
     abstract public function mark();

    /**
     * abstract method
     * Get score max possible for a question
     *
     * @access public
     *
     * @return float
     */
     abstract public function maxScore();

     /**
      * abstract method
      *
      * @access public
      * @param Integer $interId id of interaction
      *
      * @return \UJM\ExoBundle\Entity\InteractionX (qcm, graphic, open, ...)
      */
     abstract public function getInteractionX($interId);

     /**
      * abstract method
      *
      * @access public
      * @param Integer $interId id of inetraction
      * @param Symfony\Component\HttpFoundation\Session\SessionInterface $session
      * @param \UJM\ExoBundle\Entity\InteractionX (qcm, graphic, open, ...) $interactionX
      *
      */
     abstract public function getResponseGiven($interId, $session, $interactionX);

}
