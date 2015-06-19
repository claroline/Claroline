<?php

/**
 *
 * Services for the matching
 */

namespace UJM\ExoBundle\Services\classes\Interactions;

class Open extends Interaction {
    /**
     * implement the abstract method
     * To process the user's response for a paper(or a test)
     *
     * @access public
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * @return mixed[]
     */
     public function response(\Symfony\Component\HttpFoundation\Request $request, $paperID = 0)
     {
         $interactionOpenID = $request->request->get('interactionOpenToValidated');
         $tempMark = true;

         $session = $request->getSession();

         $em = $this->doctrine->getManager();
         $interOpen = $em->getRepository('UJMExoBundle:InteractionOpen')->find($interactionOpenID);

         $response = $request->request->get('interOpen');

         $penalty = $this->getPenalty($interOpen->getInteraction(), $session, $paperID);

         $score = $this->mark($interOpen, $response, $penalty);

         $res = array(
             'penalty'   => $penalty,
             'interOpen' => $interOpen,
             'response'  => $response,
             'score'     => $score,
             'tempMark'  => $tempMark
         );

        return $res;
     }

     /**
      * implement the abstract method
      * To calculate the score
      *
      * @access public
      * @param \UJM\ExoBundle\Entity\InteractionOpen $interOpen
      * @param String $response
      * @param float $penalty penalty if the user showed hints
      *
      * @return string userScore/scoreMax
      */
     public function mark(
             \UJM\ExoBundle\Entity\InteractionOpen $interOpen = null, 
             $response = null, 
             $penalty = null
     )
     {
         if ($interOpen->getTypeOpenQuestion() == 'long') {
             $score = -1;
         } else if ($interOpen->getTypeOpenQuestion() == 'oneWord') {
             $score = $this->getScoreOpenOneWord($response, $interOpen);
         } else if ($interOpen->getTypeOpenQuestion() == 'short') {
             $score = $this->getScoreShortResponse($response, $interOpen);
         }

         if ($interOpen->getTypeOpenQuestion() != 'long') {
             $score -= $penalty;
             if ($score < 0) {
                 $score = 0;
             }
         }

         $score .= '/'.$this->maxScore($interOpen);

         return $score;
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

     /**
     * Get the types of open question long, short, numeric, one word
     *
     * @access public
     *
     * @return array
     */
    public function getTypeOpen()
    {
        $em = $this->doctrine->getManager();

        $typeOpen = array();
        $types = $em->getRepository('UJMExoBundle:TypeOpenQuestion')
                    ->findAll();

        foreach ($types as $type) {
            $typeOpen[$type->getId()] = $type->getCode();
        }

        return $typeOpen;
    }

     /**
     * Get score for an open question with one word
     *
     * @access private
     *
     * @param String $response
     * @param \UJM\ExoBundle\Entity\InteractionOpen $interOpen
     *
     * @return float
     */
    private function getScoreOpenOneWord($response, $interOpen)
    {
        $score = 0;
        foreach ($interOpen->getWordResponses() as $wr) {
            $score += $this->getScoreWordResponse($wr, $response);
        }

        return $score;

    }

    /**
     * Get score for an open question with short answer
     *
     * @access private
     *
     * @param String $response
     * @param \UJM\ExoBundle\Entity\InteractionOpen $interOpen
     *
     * @return float
     */
    private function getScoreShortResponse($response, $interOpen)
    {
        $score = 0;

        foreach($interOpen->getWordResponses() as $wr) {
            $pattern = '/'.$wr->getResponse().'/';
            if (!$wr->getCaseSensitive()) {
                $pattern .= 'i';
            }
            $subject = '/'.$response.'/';
            if (preg_match($pattern, $subject)) {
                $score += $wr->getScore();
            }
        }

        return $score;
    }
}
