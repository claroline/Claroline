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
         die('service open refactoring');
         $scoreMax = 0;

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
}
