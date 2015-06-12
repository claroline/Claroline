<?php

/**
 *
 * Servives for the matching
 */

namespace UJM\ExoBundle\Services\classes\Interactions;

class open extends interaction {
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
      * Get score max possible for an open question
      *
      * @access public
      *
      * @param \UJM\ExoBundle\Entity\InteractionOpen $interOpen
      *
      * @return float
      */
     public function maxScore($interOpen = null)
     {
         $scoreMax = 0;

         if ($interOpen->getTypeOpenQuestion() == 'long') {
             $scoreMax = $interOpen->getScoreMaxLongResp();
         } else if ($interOpen->getTypeOpenQuestion() == 'oneWord') {
             $scoreMax = $this->om
                              ->getRepository('UJMExoBundle:WordResponse')
                              ->getScoreMaxOneWord($interOpen->getId());
         } else if ($interOpen->getTypeOpenQuestion() == 'short') {
             $scoreMax = $this->om
                              ->getRepository('UJMExoBundle:WordResponse')
                             ->getScoreMaxShort($interOpen->getId());
         }

         return $scoreMax;
     }

     /**
     * implement the abstract method
     *
     * @access public
     * @param Integer $interId id of interaction
     *
     * @return \UJM\ExoBundle\Entity\InteractionOpen
     */
     public function getInteractionX($interId)
     {
         $interOpen = $this->om
                          ->getRepository('UJMExoBundle:InteractionOpen')
                          ->getInteractionOpen($interId);

         return $interOpen;
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

         return $responseGiven;
     }
}
