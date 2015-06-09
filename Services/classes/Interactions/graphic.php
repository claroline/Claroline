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
      * Get score max possible for a graphic question
      *
      * @access public
      *
      * @param \UJM\ExoBundle\Entity\InteractionGraphic $interGraph
      *
      * @return float
      */
     public function maxScore($interGraph)
     {
         die('service graphic refactoring');
         $scoreMax = 0;

         return $scoreMax;
     }
}
