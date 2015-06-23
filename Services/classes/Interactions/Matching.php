<?php

/**
 *
 * Services for the matching
 */

namespace UJM\ExoBundle\Services\classes\Interactions;

use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Form\InteractionMatchingType;
use UJM\ExoBundle\Form\ResponseType;

class Matching extends Interaction {
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
     public function mark(
             \UJM\ExoBundle\Entity\InteractionMatching $interMatching = null,
             $penalty= null, $tabRightResponse= null,
             $tabResponseIndex= null
     )
     {
         $em = $this->doctrine->getManager();
         $scoretmp = 0;
         $scoreMax = $this->maxScore($interMatching);

         foreach ($tabRightResponse as $labelId => $value) {
             if ( isset($tabResponseIndex[$labelId]) && $tabRightResponse[$labelId] != null
                     && (!substr_compare($tabRightResponse[$labelId], $tabResponseIndex[$labelId], 0)) ) {
                 $label = $em->getRepository('UJMExoBundle:Label')
                             ->find($labelId);
                 $scoretmp += $label->getScoreRightResponse();
             }
             if ($tabRightResponse[$labelId] == null && !isset($tabResponseIndex[$labelId])) {
                 $label = $em->getRepository('UJMExoBundle:Label')
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
         $em = $this->doctrine->getManager();
         $interMatching = $em->getRepository('UJMExoBundle:InteractionMatching')
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
         $interactionMatching = $this->doctrine
                                     ->getManager()
                                     ->getRepository('UJMExoBundle:InteractionMatching')
                                     ->getInteractionMatching($interaction->getId());

        if ($interactionMatching->getShuffle()) {
            $interactionMatching->shuffleProposals();
            $interactionMatching->shuffleLabels();
        } else {
            $interactionMatching->sortProposals();
            $interactionMatching->sortLabels();
        }

        $form = $this->formFactory->create(new ResponseType(), $response);

        $vars['interactionToDisplayed'] = $interactionMatching;
        $vars['form'] = $form->createView();
        $vars['exoID'] = $exoID;

        return $this->templating->renderResponse('UJMExoBundle:InteractionMatching:paper.html.twig', $vars);
     }

     /**
     * Get the types of Matching, Multiple response, unique response
     *
     * @access public
     *
     * @return array
     */
    public function getTypeMatching()
    {
        $em = $this->doctrine->getManager();

        $typeMatching = array();
        $types = $em->getRepository('UJMExoBundle:TypeMatching')
                    ->findAll();

        foreach ($types as $type) {
            $typeMatching[$type->getId()] = $type->getCode();
        }

        return $typeMatching;
    }

     /**
      * For the correction of a matching question :
      * init array of responses of user indexed by labelId
      * init array of rights responses indexed by labelId
      *
      * @access public
      *
      * @param String $response
      * @param \UJM\ExoBundle\Entity\Paper\InteractionMatching $interMatching
      *
      * @return array of arrays
      */
    public function initTabResponseMatching($response, $interMatching) {

        $tabsResponses = array();

        $tabResponseIndex = $this->getTabResponseIndex($response);
        $tabRightResponse = $this->initTabRightResponse($interMatching);

        //add in $tabResponseIndex label empty
        foreach ($interMatching->getLabels() as $label) {
            if (!isset($tabResponseIndex[$label->getId()])) {
                $tabResponseIndex[$label->getId()] = null;
            }
        }


        $tabsResponses[0] = $tabResponseIndex;
        $tabsResponses[1] = $tabRightResponse;

        return $tabsResponses;

    }

    /**
     * init array of rights responses indexed by labelId
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\InteractionMatching $interMatching
     *
     * @return mixed[]
     */
    public function initTabRightResponse($interMatching) {
        $tabRightResponse = array();

        //array of rights responses indexed by labelId
        foreach ($interMatching->getProposals() as $proposal) {
            $associateLabel = $proposal->getAssociatedLabel();
            if ($associateLabel != null) {
                foreach ($associateLabel as $associatedLabel) {
                    $index = $associatedLabel->getId();
                    if (isset($tabRightResponse[$index])) {
                        $tabRightResponse[$index] .= '-' . $proposal->getId();
                    } else {
                        $tabRightResponse[$index] = $proposal->getId();
                    }
                }
            }
        }

        //add in $tabRightResponse label empty
        foreach ($interMatching->getLabels() as $label) {
            if (!isset($tabRightResponse[$label->getId()])) {
                $tabRightResponse[$label->getId()] = null;
            }
        }

        return $tabRightResponse;
    }

    /**
     * init array of student response indexed by labelId
     *
     * @access private
     *
     * @param String $response
     *
     * @return integer[]
     */
    private function getTabResponseIndex($response) {
        $tabResponse = explode(';', substr($response, 0, -1));
        $tabResponseIndex = array();

        //array of responses of user indexed by labelId
        foreach ($tabResponse as $rep) {
            $tabTmp = preg_split('(,)', $rep);
            for ($i = 1; $i < count($tabTmp);$i++) {
                if (isset($tabResponseIndex[$tabTmp[$i]])) {
                    $tabResponseIndex[$tabTmp[$i]] .= '-' . $tabTmp[0];
                } else {
                    $tabResponseIndex[$tabTmp[$i]] = $tabTmp[0];
                }
            }
        }

        return $tabResponseIndex;
    }
}
