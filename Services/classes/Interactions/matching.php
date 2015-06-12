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
         $interactionMatchingId = $request->request->get('interactionMatchingToValidated');
         $response = $request->request->get('jsonResponse');

         $em = $this->doctrine->getManager();
         $interMatching = $em->getRepository('UJMExoBundle:InteractionMatching')->find($interactionMatchingId);

         $session = $request->getSession();

         $penalty = $this->getPenalty($interMatching->getInteraction(), $session, $paperID);

         $tabsResponses = $this->initTabResponseMatching($response, $interMatching);
         $tabRightResponse = $tabsResponses[1];
         $tabResponseIndex = $tabsResponses[0];

         $score = $this->mark($interMatching, $penalty, $tabRightResponse, $tabResponseIndex);

         $res = array(
           'score'            => $score,
           'penalty'          => $penalty,
           'interMatching'    => $interMatching,
           'tabRightResponse' => $tabRightResponse,
           'tabResponseIndex' => $tabResponseIndex,
           'response'         => $response
         );

        return $res;
     }

     /**
      * implement the abstract method
      * To calculate the score
      *
      * @access public
      *
      * @param \UJM\ExoBundle\Entity\InteractionMatching $interMatching
      * @param float $penality penalty if the user showed hints
      * @param array $tabRightResponse
      * @param array $tabResponseIndex
      *
      * @return string userScore/scoreMax
      */
     public function mark(\UJM\ExoBundle\Entity\InteractionMatching $interMatching = null, $penalty= null, $tabRightResponse= null, $tabResponseIndex= null)
     {
         $scoretmp = 0;
         $scoreMax = $this->maxScore($interMatching);

         foreach ($tabRightResponse as $labelId => $value) {
             if ( isset($tabResponseIndex[$labelId]) && $tabRightResponse[$labelId] != null
                     && (!substr_compare($tabRightResponse[$labelId], $tabResponseIndex[$labelId], 0)) ) {
                 $label = $this->om->getRepository('UJMExoBundle:Label')
                                   ->find($labelId);
                 $scoretmp += $label->getScoreRightResponse();
             }
             if ($tabRightResponse[$labelId] == null && !isset($tabResponseIndex[$labelId])) {
                 $label = $this->om->getRepository('UJMExoBundle:Label')
                                   ->find($labelId);
                 $scoretmp += $label->getScoreRightResponse();
             }
         }

         $score = $scoretmp - $penalty;
         if ($score < 0) {
             $score = 0;
         }
         $score .= '/'.$scoreMax;

        return $score;

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
