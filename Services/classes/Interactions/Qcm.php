<?php

/**
 * Services for the qcm.
 */
namespace UJM\ExoBundle\Services\classes\Interactions;

class Qcm extends Interaction
{
    /**
     * implement the abstract method
     * To process the user's response for a paper(or a test).
     *
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * @return mixed[]
     */
    public function response(\Symfony\Component\HttpFoundation\Request $request, $paperID = 0)
    {
        $interactionQCMID = $request->request->get('interactionQCMToValidated');

        $em = $this->doctrine->getManager();
        $interQCM = $em->getRepository('UJMExoBundle:InteractionQCM')->find($interactionQCMID);

        $response = $this->convertResponseInArray($request->request->get('choice'), $interQCM->getTypeQCM()->getCode());
        $responseID = $this->convertResponseInChr($response);

        $allChoices = $interQCM->getChoices();

        $session = $request->getSession();
        $penalty = $this->getPenalty($interQCM->getInteraction(), $session, $paperID);

        $score = $this->mark($interQCM, $response, $allChoices, $penalty);

        $res = array(
            'score' => $score,
            'penalty' => $penalty,
            'interQCM' => $interQCM,
            'response' => $responseID,
        );

        return $res;
    }

    /**
     * implement the abstract method
     * To calculate the score for a QCM.
     *
     *
     * @param \UJM\ExoBundle\Entity\InteractionQCM $interQCM
     * @param array [integer] $response array of id Choice selected
     * @param array [UJM\ExoBundle\Entity\Choice] $allChoices choices linked at the QCM
     * @param float $penalty penalty if the user showed hints
     *
     * @return string userScore/scoreMax
     */
    public function mark(
        \UJM\ExoBundle\Entity\InteractionQCM $interQCM = null,
        array $response = null,
        $allChoices = null,
        $penalty = null
    )
    {
        $score = 0;
        $scoreMax = $this->maxScore($interQCM);

        if (!$interQCM->getWeightResponse()) {
            $score = $this->markGlobal($allChoices, $response, $interQCM, $penalty) . '/' . $scoreMax;
        } else {
            $score = $this->markWeightResponse($allChoices, $response, $penalty, $scoreMax) . '/' . $scoreMax;
        }

        return $score;
    }

    /**
     * implement the abstract method
     * Get score max possible for a QCM.
     *
     *
     * @param \UJM\ExoBundle\Entity\InteractionQCM $interQCM
     *
     * @return float
     */
    public function maxScore($interQCM = null)
    {
        $scoreMax = 0;

        if (!$interQCM->getWeightResponse()) {
            $scoreMax = $interQCM->getScoreRightResponse();
        } else {
            foreach ($interQCM->getChoices() as $choice) {
                if ($choice->getRightResponse()) {
                    $scoreMax += $choice->getWeight();
                }
            }
        }

        return $scoreMax;
    }

    /**
     * implement the abstract method.
     *
     * @param int $questionId
     *
     * @return \UJM\ExoBundle\Entity\InteractionQCM
     */
    public function getInteractionX($questionId)
    {
        return $this->doctrine->getManager()
            ->getRepository('UJMExoBundle:InteractionQCM')
            ->findOneByQuestion($questionId);
    }

    /**
     * implement the abstract method.
     *
     * call getAlreadyResponded and prepare the interaction to displayed if necessary
     *
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
            $interactionX->shuffleChoices();
        } else {
            $interactionX->sortChoices();
        }

        return $responseGiven;
    }

    /**
     * Get the types of QCM, Multiple response, unique response.
     *
     *
     * @return array
     */
    public function getTypeQCM()
    {
        $em = $this->doctrine->getManager();

        $typeQCM = array();
        $types = $em->getRepository('UJMExoBundle:TypeQCM')
            ->findAll();

        foreach ($types as $type) {
            $typeQCM[$type->getId()] = $type->getCode();
        }

        return $typeQCM;
    }

    /**
     * Get response in array.
     *
     *
     * @param array [integer] or int $response
     * @param int $qcmCode type of qcm (multiple or simple)
     *
     * @return integer[]
     */
    private function convertResponseInArray($resp, $qcmCode)
    {
        $response = array();

        if ($qcmCode == 2) {
            $response[] = $resp;
        } else {
            if ($resp != null) {
                $response = $resp;
            }
        }

        return $response;
    }

    /**
     * Get response in String.
     *
     *
     * @param array [integer] or int $response
     *
     * @return String
     */
    private function convertResponseInChr($response)
    {
        $responseID = '';

        foreach ($response as $res) {
            if ($res != null) {
                $responseID .= $res . ';';
            }
        }

        return $responseID;
    }

    /**
     * Calculate the score with weightResponse.
     *
     *
     * @param array [UJM\ExoBundle\Entity\Choice] $allChoices choices linked at the QCM
     * @param array [integer] $response array of id Choice selected
     * @param float $penalty penalty if the user showed hints
     *
     * @return float
     */
    private function markWeightResponse($allChoices, $response, $penalty, $scoreMax)
    {
        $score = 0;
        $markByChoice = array();
        foreach ($allChoices as $choice) {
            $markByChoice[(string)$choice->getId()] = $choice->getWeight();
        }
        if ($response[0] != null) {
            foreach ($response as $res) {
                $score += $markByChoice[$res];
            }
        }

        if ($score > $scoreMax) {
            $score = $scoreMax;
        }

        $score -= $penalty;

        if ($score < 0) {
            $score = 0;
        }

        return $score;
    }

    /**
     * Calculate the score with global mark.
     *
     *
     * @param array [\UJM\ExoBundle\Entity\Choice] $allChoices choices linked at the QCM
     * @param array [integer] $response array of id Choice selected
     * @param \UJM\ExoBundle\Entity\InteractionQCM $interQCM
     * @param float $penalty penalty if the user showed hints
     *
     * @return float
     */
    private function markGlobal($allChoices, $response, $interQCM, $penalty)
    {
        $score = 0;
        $rightChoices = array();
        foreach ($allChoices as $choice) {
            if ($choice->getRightResponse()) {
                $rightChoices[] = (string)$choice->getId();
            }
        }

        $result = array_diff($response, $rightChoices);
        $resultBis = array_diff($rightChoices, $response);

        if ((count($result) == 0) && (count($resultBis) == 0)) {
            $score = $interQCM->getScoreRightResponse() - $penalty;
        } else {
            $score = $interQCM->getScoreFalseResponse() - $penalty;
        }
        if ($score < 0) {
            $score = 0;
        }

        return $score;
    }
}
