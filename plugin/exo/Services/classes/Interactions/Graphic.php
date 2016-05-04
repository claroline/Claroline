<?php

namespace UJM\ExoBundle\Services\classes\Interactions;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Services for the graphic.
 *
 * @DI\Service("ujm.exo.graphic_service")
 */
class Graphic extends Interaction
{
    /**
     * implement the abstract method
     * To process the user's response for a paper(or a test).
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int                                       $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * @return mixed[]
     */
    public function response(Request $request, $paperID = 0)
    {
        $answers = $request->request->get('answers'); // Answer of the student
        $graphId = $request->request->get('graphId'); // Id of the graphic interaction

        $em = $this->doctrine->getManager();

        $rightCoords = $em->getRepository('UJMExoBundle:Coords')
            ->findBy(array('interactionGraphic' => $graphId));

        $interG = $em->getRepository('UJMExoBundle:InteractionGraphic')
            ->find($graphId);

        $doc = $em->getRepository('UJMExoBundle:Picture')
            ->findOneBy(array('id' => $interG->getPicture()));

        if (!preg_match('/[0-9]+/', $answers)) {
            $answers = '';
        }

        $penalty = $this->getPenalty($interG->getQuestion(), $request->getSession(), $paperID);
        $score = $this->mark($answers, $rightCoords, $penalty);
        $total = $this->maxScore($interG); // Score max

        $res = array(
            'penalty' => $penalty, // Penalty (hints)
            'interG' => $interG, // The entity interaction graphic (for the id ...)
            'coords' => $rightCoords, // The coordinates of the right answer zones
            'doc' => $doc, // The answer picture (label, src ...)
            'total' => $total, // Score max if all answers right and no penalty
            'rep' => preg_split('[;]', $answers), // Coordinates of the answer zones of the student's answer
            'score' => $score, // Score of the student (right answer - penalty)
            'response' => $answers, // The student's answer (with all the information of the coordinates)
        );

        return $res;
    }

    /**
     * implement the abstract method
     * To calculate the score.
     *
     * @param string                         $answer
     * @param \UJM\ExoBundle\Entity\Coords[] $rightCoords
     * @param number                         $penalty
     *
     * @return float
     */
    public function mark($answer = null, array $rightCoords = null, $penalty = null)
    {
        $score = 0;

        // Get the list of submitted coords from the answer string
        $coordsList = preg_split('/[;,]/', $answer);
        if (!empty($coordsList)) {
            // Loop through correct answers to know if they are in the submitted data
            foreach ($rightCoords as $expected) {
                // Get X and Y values from expected string
                list($xr, $yr) = explode(',', $expected->getValue());
                // Get tolerance zone
                $zoneSize = $expected->getSize();

                foreach ($coordsList as $coords) {
                    if (preg_match('/[0-9]+/', $coords)) {
                        // Get X and Y values from answers of the student
                        list($xa, $ya) = explode('-', $coords);

                        if (($xa <= ($xr + $zoneSize)) && ($xa > $xr) &&
                            ($ya <= ($yr + $zoneSize)) && ($ya > $yr)
                        ) {
                            // The student answer is in the answer zone give him the points
                            $score += $expected->getScoreCoords();

                            break; // We have found an answer for this answer zone, so we directly pass to the next one
                        }
                    }
                }
            }
        }

        if ($penalty) {
            $score = $score - $penalty; // Score of the student with penalty
        }

        // Not negative score
        if ($score < 0) {
            $score = 0;
        }

        return $score;
    }

    /**
     * implement the abstract method
     * Get score max possible for a graphic question.
     *
     * @param \UJM\ExoBundle\Entity\InteractionGraphic $interGraph
     *
     * @return float
     */
    public function maxScore($interGraph = null)
    {
        $em = $this->doctrine->getManager();
        $scoreMax = 0;

        $rightCoords = $em->getRepository('UJMExoBundle:Coords')
            ->findBy(array('interactionGraphic' => $interGraph->getId()));

        foreach ($rightCoords as $score) {
            $scoreMax += $score->getScoreCoords();
        }

        return $scoreMax;
    }

    /**
     * implement the abstract method.
     *
     * @param int $questionId
     *
     * @return \UJM\ExoBundle\Entity\InteractionGraphic
     */
    public function getInteractionX($questionId)
    {
        return $this->doctrine->getManager()
            ->getRepository('UJMExoBundle:InteractionGraphic')
            ->findOneByQuestion($questionId);
    }

    /**
     * implement the abstract method.
     *
     * call getAlreadyResponded and prepare the interaction to displayed if necessary
     *
     * @param \UJM\ExoBundle\Entity\Interaction                            $interactionToDisplay interaction (question) to displayed
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface   $session
     * @param \UJM\ExoBundle\Entity\InteractionX (qcm, graphic, open, ...) $interactionX
     *
     * @return \UJM\ExoBundle\Entity\Response
     */
    public function getResponseGiven($interactionToDisplay, SessionInterface $session, $interactionX)
    {
        $responseGiven = $this->getAlreadyResponded($interactionToDisplay, $session);

        return $responseGiven;
    }
}
