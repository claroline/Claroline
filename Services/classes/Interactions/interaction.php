<?php

/**
 * abstract class
 *
 */

namespace UJM\ExoBundle\Services\classes\Interactions;

use Doctrine\Bundle\DoctrineBundle\Registry;


abstract class interaction {

    protected $doctrine;
    protected $om;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Claroline\CoreBundle\Persistence\ObjectManager $om Dependency Injection
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection;
     *
     */
    public function __construct(
        Registry $doctrine, ObjectManager $om
    )
    {
        $this->doctrine = $doctrine;
        $this->om       = $om;
    }

    /**
     * For an interaction know if it's linked with response and if it's shared
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     *
     * @return array[boolean]
     */
    public function getActionInteraction(\UJM\ExoBundle\Entity\Interaction $interaction)
    {
        $response = $this->doctrine->getManager()
                         ->getRepository('UJMExoBundle:Response')
            ->findBy(array('interaction' => $interaction->getId()));
        if (count($response) > 0) {
            $questionWithResponse[$interaction->getId()] = 1;
        } else {
            $questionWithResponse[$interaction->getId()] = 0;
        }

        $share = $em->getRepository('UJMExoBundle:Share')
            ->findBy(array('question' => $interaction->getQuestion()->getId()));
        if (count($share) > 0) {
            $alreadyShared[$interaction->getQuestion()->getId()] = 1;
        } else {
            $alreadyShared[$interaction->getQuestion()->getId()] = 0;
        }

        $actions[0] = $questionWithResponse;
        $actions[1] = $alreadyShared;

        return $actions;
    }

    /**
     * For an shared interaction whith me, know if it's linked with response and if I can modify it
     *
     * @access public
     *
     * @param Doctrine EntityManager $em
     * @param \UJM\ExoBundle\Entity\Share $shared
     *
     * @return array
     */
    public function getActionShared($shared)
    {
        $em = $this->doctrine->getEntityManager();
        $inter = $em->getRepository('UJMExoBundle:Interaction')
                ->findOneBy(array('question' => $shared->getQuestion()->getId()));

        $sharedWithMe[$shared->getQuestion()->getId()] = $inter;
        $shareRight[$inter->getId()] = $shared->getAllowToModify();

        $response = $em->getRepository('UJMExoBundle:Response')
            ->findBy(array('interaction' => $inter->getId()));

        if (count($response) > 0) {
            $questionWithResponse[$inter->getId()] = 1;
        } else {
            $questionWithResponse[$inter->getId()] = 0;
        }

        $actionsS[0] = $sharedWithMe;
        $actionsS[1] = $shareRight;
        $actionsS[2] = $questionWithResponse;

        return $actionsS;
    }

    /**
     * Get penalty for an interaction and a paper
     *
     * @access private
     *
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     * @param integer $paperID id Paper
     *
     * @return array
     */
    private function getPenaltyPaper($interaction, $paperID)
    {
        $penalty = 0;

        $hints = $interaction->getHints();

        foreach ($hints as $hint) {
            $lhp = $this->om
                        ->getRepository('UJMExoBundle:LinkHintPaper')
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
     * Get penalty for a test or a paper
     *
     * @access protected
     *
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param int $paperID
     *
     * @return int
     */
    protected function getPenalty($interaction, \Symfony\Component\HttpFoundation\Session\SessionInterface $session, $paperID)
    {
        if ($paperID == 0) {
            if ($session->get('penalties')) {
                $penalty = 0;
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
            $penalty = $this->getPenaltyPaper($interaction, $paperID);
        }

        return $penalty;
    }

    /**
     * abstract method
     * To process the user's response for a paper(or a test)
     *
     * @access public
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * @return array
     */
     abstract public function response(\Symfony\Component\HttpFoundation\Request $request, $paperID = 0);

     /**
     * abstract method
     * To calculate the score for a question
     *
     * @access public
     *
     * @return string userScore/scoreMax
     */
     abstract public function mark();

    /**
     * abstract method
     * Get score max possible for a question
     *
     * @access public
     *
     * @return float
     */
     abstract public function maxScore();
}
