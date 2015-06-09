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
     public function response()
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
     public function maxScore($interOpen)
     {
         die('service open refactoring');
         $scoreMax = 0;

         return $scoreMax;
     }
}
