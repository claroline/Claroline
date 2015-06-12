<?php

/**
 *
 * Servives for the graphic
 */

namespace UJM\ExoBundle\Services\classes\Interactions;

class graphic extends interaction {

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
      * Get score max possible for a graphic question
      *
      * @access public
      *
      * @param \UJM\ExoBundle\Entity\InteractionGraphic $interGraph
      *
      * @return float
      */
     public function maxScore($interGraph = null)
     {
         $scoreMax = 0;

         $rightCoords = $this->om
                            ->getRepository('UJMExoBundle:Coords')
                            ->findBy(array('interactionGraphic' => $interGraph->getId()));

         foreach ($rightCoords as $score) {
             $scoreMax += $score->getScoreCoords();
         }

         return $scoreMax;
     }

     /**
     * implement the abstract method
     *
     * @access public
     * @param Integer $interId id of interaction
     *
     * @return \UJM\ExoBundle\Entity\InteractionGraphic
     */
     public function getInteractionX($interId)
     {
         $interGraphic = $this->om
                          ->getRepository('UJMExoBundle:InteractionGraphic')
                          ->getInteractionGraphic($interId);

         return $interGraphic;
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
