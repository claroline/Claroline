<?php

/**
 *
 * Services for the hole
 */

namespace UJM\ExoBundle\Services\classes\Interactions;

use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Form\InteractionHoleType;
use UJM\ExoBundle\Form\ResponseType;

class Hole extends Interaction {

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
        $em = $this->doctrine->getManager();
        $interactionHoleID = $request->request->get('interactionHoleToValidated');

        $session = $request->getSession();

        $interHole = $em->getRepository('UJMExoBundle:InteractionHole')->find($interactionHoleID);

        $penalty = $this->getPenalty($interHole->getInteraction(), $session, $paperID);

        $score = $this->mark($interHole, $request->request, $penalty);

        $response = $this->getJsonResponse($interHole, $request);

        $res = array(
            'penalty'   => $penalty,
            'interHole' => $interHole,
            'response'  => $response,
            'score'     => $score
        );

        return $res;
     }

     /**
      * implement the abstract method
      * To calculate the score
      *
      * @access public
      * @param \UJM\ExoBundle\Entity\InteractionHole $interHole
      * @param \Symfony\Component\HttpFoundation\Request $request
      * @param float $penalty penalty if the user showed hints
      *
      * @return string userScore/scoreMax
      */
     public function mark(
             \UJM\ExoBundle\Entity\InteractionHole $interHole = null,
             $request = null,
             $penalty = null
     )
     {
         $score = 0;
         $scoreMax = $this->maxScore($interHole);

         foreach($interHole->getHoles() as $hole) {
             $response = $request->get('blank_'.$hole->getPosition());
             $response = trim($response);
             $response = preg_replace('/\s+/', ' ', $response);
             $score += $this->getScoreHole($hole, $response);
         }

         $score -= $penalty;

         if ($score < 0) {
             $score = 0;
         }

         $score .= '/'.$scoreMax;

         return $score;
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
         $scoreMax = 0;
         foreach ($interHole->getHoles() as $hole) {
             $scoretemp = 0;
             foreach ($hole->getWordResponses() as $wr) {
                 if ($wr->getScore() > $scoretemp) {
                     $scoretemp = $wr->getScore();
                 }
             }
             $scoreMax += $scoretemp;
         }

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
         $em = $this->doctrine->getManager();
         $interHole = $em->getRepository('UJMExoBundle:InteractionHole')
                         ->getInteractionHole($interId);

         return $interHole;
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
      * implements the abstract method
      *
      * @access public
      *
      * @param \UJM\ExoBundle\Entity\Interaction $interaction
      * @param integer $exoID
      * @param mixed[] An array of parameters to pass to the view
      *
      * @return \Symfony\Component\HttpFoundation\Response
      */
     public function show($interaction, $exoID, $vars)
     {
         $response = new Response();
         $interactionHole = $this->doctrine
                                 ->getManager()
                                 ->getRepository('UJMExoBundle:InteractionHole')
                                 ->getInteractionHole($interaction->getId());

         $form   = $this->formFactory->create(new ResponseType(), $response);

         $vars['interactionToDisplayed'] = $interactionHole;
         $vars['form']            = $form->createView();
         $vars['exoID']           = $exoID;

         return $this->templating->renderResponse('UJMExoBundle:InteractionHole:paper.html.twig', $vars);
     }

     /**
      * implements the abstract method
      *
      * @access public
      *
      * @param \UJM\ExoBundle\Entity\Interaction $interaction
      * @param integer $exoID
      * @param integer $catID
      * @param Claroline\Entity\User $user
      *
      * @return \Symfony\Component\HttpFoundation\Response
      */
     public function edit($interaction, $exoID, $catID, $user)
     {
         $interactionHole = $this->doctrine
                                 ->getManager()
                                 ->getRepository('UJMExoBundle:InteractionHole')
                                 ->getInteractionHole($interaction->getId());

         $editForm = $this->formFactory->create(
             new InteractionHoleType($user, $catID), $interactionHole
         );

         $linkedCategory = $this->questionService->getLinkedCategories();

         return $this->templating->renderResponse(
             'UJMExoBundle:InteractionHole:edit.html.twig', array(
             'entity'         => $interactionHole,
             'edit_form'      => $editForm->createView(),
             'nbResponses'    => $this->getNbReponses($interaction),
             'linkedCategory' => $linkedCategory,
             'exoID'          => $exoID,
             'locker'         => $this->categoryService->getLockCategory()
             )
         );
     }

     /**
      *
      * @access private
      * @param \UJM\ExoBundle\Entity\InteractionHole $interHole
      * @param \Symfony\Component\HttpFoundation\Request $request
      *
      * @return json
      */
     private function getJsonResponse($interHole, $request)
     {
         $em = $this->doctrine->getManager();
         foreach($interHole->getHoles() as $hole) {
             $response = $request->get('blank_'.$hole->getPosition());
             $response = trim($response);
             $response = preg_replace('/\s+/', ' ', $response);

             if ($hole->getSelector()) {
                 $wr = $em->getRepository('UJMExoBundle:WordResponse')->find($response);
                 $tabResp[$hole->getPosition()] = $wr->getResponse();
             } else {
                 $from = array("'", '"');
                 $to = array("\u0027","\u0022");
                 $tabResp[$hole->getPosition()] = str_replace($from, $to, $response);
             }
         }

         return json_encode($tabResp);
     }

     /**
      *
      * @access private
      * @param \UJM\ExoBundle\Entity\Hole $hole
      * @param String $response
      *
      * @return float
      */
     private function getScoreHole($hole, $response)
     {
         $em = $this->doctrine->getManager();
         if ($hole->getSelector() == true) {
             $wr = $em->getRepository('UJMExoBundle:WordResponse')->find($response);
             $mark = $wr->getScore();
         } else {
             foreach ($hole->getWordResponses() as $wr) {
                 $mark = $this->getScoreWordResponse($wr, $response);
             }
         }

         return $mark;
     }
}
