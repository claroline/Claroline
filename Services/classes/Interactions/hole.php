<?php

/**
 *
 * Servives for the hole
 */

namespace UJM\ExoBundle\Services\classes\Interactions;

class hole extends interaction {
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
      * Get score max possible for a question with holes question
      *
      * @access public
      *
      * @param \UJM\ExoBundle\Entity\InteractionHole $interHole
      *
      * @return float
      */
     public function maxScore($interHole = null)
     {
         die('service hole refactoring');
         $scoreMax = 0;

         return $scoreMax;
     }

     /**
     * implement the abstract method
     *
     * @access public
     * @param Integer $interId id of interaction
     *
     * @return \UJM\ExoBundle\Entity\InteractionHole
     */
     public function getInteractionX($interId)
     {
         $interHole = $this->om
                          ->getRepository('UJMExoBundle:InteractionHole')
                          ->getInteractionHole($interId);

         return $interHole;
     }
}
