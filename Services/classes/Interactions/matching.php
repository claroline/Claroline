<?php

/**
 *
 * Servives for the matching
 */

namespace UJM\ExoBundle\Services\classes\Interactions;

class matching extends interaction {
    /**
     * implement the abstract method
     * To process the user's response for a paper(or a test)
     *
     * @access public
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * @return array
     */
     public function response(\Symfony\Component\HttpFoundation\Request $request, $paperID = 0)
     {

     }

     /**
     * implement the abstract method
     * To calculate the score
     *
     * @access public
     *
     * @return string userScore/scoreMax
     */
     public function mark()
     {

     }

    /**
      * implement the abstract method
      * Get score max possible for a matching question
      *
      * @access public
      *
      * @param \UJM\ExoBundle\Entity\InteractionMatching $interMatching
      *
      * @return float
      */
     public function maxScore($interMatching = null)
     {
         $scoreMax = 0;

         foreach ($interMatching->getLabels() as $label) {
             $scoreMax += $label->getScoreRightResponse();
         }

         return $scoreMax;
     }

     /**
     * implement the abstract method
     *
     * @access public
     * @param Integer $interId id of interaction
     *
     * @return \UJM\ExoBundle\Entity\InteractionMatching
     */
     public function getInteractionX($interId)
     {
         $interMatching = $this->om
                          ->getRepository('UJMExoBundle:InteractionMatching')
                          ->getInteractionMatching($interId);

         return $interMatching;
     }

     /**
      * implement the abstract method
      *
      * call getAlreadyResponded and prepare the interaction to displayed if necessary
      *
      * @access public
      * @param \UJM\ExoBundle\Entity\Interaction $interactionToDisplay interaction (question) to displayed
      * @param Symfony\Component\HttpFoundation\Session\SessionInterface $session
      * @param \UJM\ExoBundle\Entity\InteractionX (qcm, graphic, open, ...) $interactionX
      *
      * @return \UJM\ExoBundle\Entity\Response
      */
     public function getResponseGiven($interactionToDisplay, $session, $interactionX)
     {
         $responseGiven = $this->getAlreadyResponded($interactionToDisplay, $session);

         if ($interactionX->getShuffle()) {
             $interactionX->shuffleProposals();
             $interactionX->shuffleLabels();
         } else {
             $interactionX->sortProposals();
             $interactionX->sortLabels();
         }

         return $responseGiven;
     }
}
