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
         die('service matching refactoring');
         $scoreMax = 0;

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
}
