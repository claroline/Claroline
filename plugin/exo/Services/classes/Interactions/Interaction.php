<?php

/**
 * abstract class.
 */

namespace UJM\ExoBundle\Services\classes\Interactions;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use JMS\DiExtraBundle\Annotation as DI;

abstract class Interaction
{
    protected $doctrine;

    /**
     * @DI\InjectParams({
     *     "doctrine"   = @DI\Inject("doctrine")
     * })
     * 
     * @param Registry $doctrine
     */
    public function __construct(
        Registry $doctrine

    ) {
        $this->doctrine = $doctrine;
    }

    /**
     * Get penalty for an interaction and a paper.
     *
     * @param \UJM\ExoBundle\Entity\Question $question
     * @param int                            $paperID
     *
     * @return float
     */
    private function getPenaltyPaper($question, $paperID)
    {
        $em = $this->doctrine->getManager();
        $penalty = 0;

        $hints = $question->getHints();

        foreach ($hints as $hint) {
            $lhp = $em->getRepository('UJMExoBundle:LinkHintPaper')
                      ->getLHP($hint->getId(), $paperID);
            if (count($lhp) > 0) {
                $signe = substr($hint->getPenalty(), 0, 1);

                if ($signe == '-') {
                    $penalty += substr($hint->getPenalty(), 1);
                } else {
                    $penalty += $hint->getPenalty();
                }
            }
        }

        return $penalty;
    }

    /**
     * Get penalty for a test or a paper.
     *
     * @param \UJM\ExoBundle\Entity\Question                             $question
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param int                                                        $paperID
     *
     * @return float
     */
    protected function getPenalty($question, SessionInterface $session, $paperID)
    {
        $penalty = 0;
        if ($paperID == 0) {
            if ($session->get('penalties')) {
                foreach ($session->get('penalties') as $penal) {
                    $signe = substr($penal, 0, 1); // In order to manage the symbol of the penalty

                    if ($signe == '-') {
                        $penalty += substr($penal, 1);
                    } else {
                        $penalty += $penal;
                    }
                }
            }
            $session->remove('penalties');
        } else {
            $penalty = $this->getPenaltyPaper($question, $paperID);
        }

        return $penalty;
    }

     /**
      * Get score for a question with key word.
      *
      *
      * @param \UJM\ExoBundle\Entity\WordResponse $wr
      * @param string $response
      *
      * @return float
      */
     protected function getScoreWordResponse($wr, $response)
     {
         $score = 0;
         if (((strcasecmp(trim($wr->getResponse()), trim($response)) == 0
                 && $wr->getCaseSensitive() == false))
                     || (trim($wr->getResponse()) == trim($response))) {
             $score = $wr->getScore();
         }

         return $score;
     }

     /**
      * Find if exist already an answer.
      *
      * @param \UJM\ExoBundle\Entity\AbstractInteraction $interactionToDisplay interaction (question) to displayed
      * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
      *
      * @return \UJM\ExoBundle\Entity\Response
      */
     public function getAlreadyResponded($interactionToDisplay, $session)
     {
         $responseGiven = $this->doctrine
                               ->getManager()
                               ->getRepository('UJMExoBundle:Response')
                               ->getAlreadyResponded($session->get('paper'), $interactionToDisplay->getId());

         if (count($responseGiven) > 0) {
             $responseGiven = $responseGiven[0]->getResponse();
         } else {
             $responseGiven = '';
         }

         return $responseGiven;
     }

     /**
      * @param \UJM\ExoBundle\Entity\Interaction $interaction
      *
      * @return int
      */
     public function getNbReponses($interaction)
     {
         $em = $this->doctrine->getEntityManager();
         $response = $em->getRepository('UJMExoBundle:Response')
                        ->findBy(array('question' => $interaction->getId()));

         return count($response);
     }

     /**
      * abstract method
      * To process the user's response for a paper(or a test).
      *
      *
      * @param \Symfony\Component\HttpFoundation\Request $request
      * @param int $paperID id Paper or 0 if it's just a question test and not a paper
      *
      * @return array
      */
     abstract public function response(Request $request, $paperID = 0);

     /**
      * abstract method
      * To calculate the score for a question.
      *
      *
      * @return float userScore
      */
     abstract public function mark();

     /**
      * abstract method
      * Get score max possible for a question.
      *
      *
      * @return float
      */
     abstract public function maxScore();

     /**
      * abstract method.
      *
      * @param int $questionId
      *
      * @return \UJM\ExoBundle\Entity\InteractionX (qcm, graphic, open, ...)
      */
     abstract public function getInteractionX($questionId);

     /**
      * abstract method.
      *
      * @param int $interId id of inetraction
      * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
      * @param \UJM\ExoBundle\Entity\InteractionX (qcm, graphic, open, ...) $interactionX
      */
     abstract public function getResponseGiven($interId, SessionInterface $session, $interactionX);
}
